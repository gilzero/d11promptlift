<?php

namespace Drupal\ai_provider_anythingllm\Plugin\search_api\backend;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Session;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\Core\Session\AnonymousUserSession;
use Drupal\ai\AiProviderPluginManager;
use Drupal\ai\AiVdbProviderPluginManager;
use Drupal\ai\OperationType\Embeddings\EmbeddingsInput;
use Drupal\ai_search\Backend\AiSearchBackendPluginBase;
use Drupal\ai_search\EmbeddingStrategyPluginManager;
use Drupal\search_api\Backend\BackendPluginBase;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\Item\FieldInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Query\QueryInterface;
use League\HTMLToMarkdown\Converter\TableConverter;
use League\HTMLToMarkdown\HtmlConverter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * AI AnythingLLM Search backend for search api.
 *
 * @SearchApiBackend(
 *   id = "search_api_ai_anythingllm",
 *   label = @Translation("AI Search with AnythingLLM"),
 *   description = @Translation("Index items on AnythingLLM.")
 * )
 */
class SearchApiAiAnythingllmBackend extends BackendPluginBase implements PluginFormInterface {


  /**
   * The AI LLM Provider.
   *
   * @var \Drupal\ai\AiProviderPluginManager
   */
  protected AiProviderPluginManager $aiProviderManager;

  /**
   * Messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected EntityFieldManagerInterface $entityFieldManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current account, proxy interface.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Max retries for iterating for access.
   *
   * @var int
   */
  protected int $maxAccessRetries = 10;

  /**
   * The account switcher service.
   *
   * @var \Drupal\Core\Session\AccountSwitcherInterface
   */
  protected $accountSwitcher;

  /**
   * Html to Markup converter.
   *
   * @var \League\HTMLToMarkdown\HtmlConverter
   */
  protected $converter;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->aiProviderManager = $container->get('ai.provider');
    $instance->entityFieldManager = $container->get('entity_field.manager');
    $instance->messenger = $container->get('messenger');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->currentUser = $container->get('current_user');
    $instance->accountSwitcher = $container->get('account_switcher');
    $instance->converter = new HtmlConverter();
    $instance->converter->getConfig()->setOption('strip_tags', TRUE);
    $instance->converter->getConfig()->setOption('strip_placeholder_links', TRUE);
    $instance->converter->getEnvironment()->addConverter(new TableConverter());

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getDiscouragedProcessors(): array {
    return [
      // We convert to markdown which LLMs understand.
      'html_filter',
      // Boosting does not apply here.
      'number_field_boost',
      // There is no point, vectors inherently do not need this.
      'stemmer',
      // We use our own more advanced embedding strategies.
      'tokenizer',
      // Boosting does not apply here.
      'type_boost',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $config = parent::defaultConfiguration();
    if (!isset($config['chat_model'])) {
      $config['chat_model'] = NULL;
    }
    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    // If a subform is received, we want the full form state.
    if ($form_state instanceof SubformStateInterface) {
      $form_state = $form_state->getCompleteFormState();
    }

    // If no provider is installed we can't do anything.
    $errors = [];
    if (!$this->aiProviderManager->hasProvidersForOperationType('ai_search_api')) {
      $errors[] = '<div class="ai-error">' . $this->t('No AI providers are installed for Embeddings calls. Choose a provider from the <a href="@ai">AI module homepage</a>, add it to your project, then %install and %configure it first.', [
        '%ai' => 'https://www.drupal.org/project/ai',
        '%install' => Link::createFromRoute($this->t('install'), 'system.modules_list')->toString(),
        '%configure' => Link::createFromRoute($this->t('configure'), 'ai.admin_providers')->toString(),
      ]) . '</div>';
    }

    if (count($errors)) {
      $form['markup'] = [
        '#markup' => implode('', $errors),
      ];
      return $form;
    }

    foreach ($this->aiProviderManager->getProvidersForOperationType('ai_search_api') as $id => $definition) {
      $provider = $this->aiProviderManager->createInstance($id);
      foreach ($provider->getConfiguredModels('ai_search_api') as $model => $label) {
        $supported_models[$id . '__' . $model] = $definition['label']->__toString() . ' | ' . $label;
      }
    }

    $form['chat_model'] = [
      '#type' => 'select',
      '#title' => $this->t('Provider & Model (Workspace)'),
      '#description' => $this->t('Choose your provider and workspace.'),
      '#default_value' => $this->configuration['chat_model'] ?? $default_model,
      '#options' => $supported_models,
      '#required' => TRUE,
      '#weight' => 2,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->setConfiguration($form_state->getValues());
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function indexItems(IndexInterface $index, array $items): array {
    [$provider_id, $model_id] = explode('__', $this->configuration['chat_model']);

    $index_name = $index->id();
    $client = $this->getClient();
    $stored_ids = [];
    $view_mode = 'default';
    foreach ($items as $id => $item) {
      // Get entity and entity_type_id.
      $entity = $item->getOriginalObject()->getEntity();
      $entity_type_id = $entity->getEntityTypeId();

      if (!empty($entity_type_id)) {
        // Get fields of item.
        $item_fields = $item->getFields();
        $item_fields += $this->getSpecialFields($index, $item);

        // Get metadata from fields.
        $meta = $this->groupFieldData($item_fields, $index);

        // Override static metadata.
        $meta['docSource'] = $id;
        $meta['title'] = $index_name . '--' . $id;

        // Generate Content Object.
        $this->accountSwitcher->switchTo(new AnonymousUserSession());
        $viewBuilder = $this->entityTypeManager->getViewBuilder($entity_type_id);
        $content = $viewBuilder->view($entity, $view_mode, $item->getLanguage());
        $content = \Drupal::service('renderer')->render($content);
        $content = $this->converter->convert((string) $content);
        $this->accountSwitcher->switchBack();

        // Store content and metadata.
        if ($this->getClient()->storeRaw($content, $meta, $index_name, $model_id)) {
          $stored_ids[] = $id;
        }
      }
    }

    return $stored_ids;
  }

  /**
   * Group the fields into title, contextual content, and main content.
   *
   * @param array $fields
   *   The Search API fields.
   * @param \Drupal\search_api\IndexInterface $index
   *   The Search API index.
   *
   * @return array
   *   The title, contextual content, and main content.
   */
  public function groupFieldData(array $fields, IndexInterface $index): array {
    $meta = [];

    foreach ($fields as $field) {

      // The fields original comes from the Search API
      // ItemInterface::getFields() method. Ensure that is still the case.
      if (!$field instanceof FieldInterface) {
        continue;
      }

      // Get and flatten the value to prepare for conversion to vector.
      $value = $this->getValue($field, TRUE);
      if (is_array($value)) {
        $value = implode(', ', $value);
      }

      $meta[$field->getLabel()] = $value;
    }

    return $meta;
  }

  /**
   * Concatenates multi-value fields.
   *
   * @param \Drupal\search_api\Item\FieldInterface $field
   *   The Search API field.
   * @param bool $convert_to_label
   *   Convert entity reference target IDs to labels.
   *
   * @return int|string|bool|float
   *   The field value.
   */
  protected function getValue(FieldInterface $field, bool $convert_to_label): int|array|string|bool|float {
    $values = $field->getValues();
    try {

      // If the field type is a reference field and its intended to be rendered
      // as fulltext or a string.
      $definition = $field->getDataDefinition();
      $settings = $definition->getSettings();
      if (
        in_array($field->getType(), ['fulltext', 'string'])
        && $definition->getDataType() === 'field_item:entity_reference'
        && !empty($settings['target_type'])
      ) {

        // If we can get the entity storage and verify the first entity is
        // an entity, clear the values and start replacing them with the labels.
        $storage = $this->entityTypeManager->getStorage($settings['target_type']);
        $entities = $storage->loadMultiple($values);
        if ($entities && reset($entities) instanceof EntityInterface) {
          $values = [];
          foreach ($entities as $entity) {
            if (!$entity instanceof EntityInterface) {
              continue;
            }
            $values[$entity->id()] = $entity->label();
          }
        }
      }
    }
    catch (\Exception $exception) {
      // Do nothing, we can just index the values for this type of field.
    }

    // Always composite if field supports multiple. Otherwise, if the field is
    // a single value, we can choose base on the field type At some point we
    // probably need to consider what field types the Vector Database supports
    // as metadata, but for now let's assume, strings, floats, integers, and
    // boolean values are fine for all.
    if (in_array($field->getType(), ['date', 'boolean', 'integer']) && count($values) === 1) {
      return (int) reset($values);
    }
    elseif (in_array($field->getType(), ['boolean']) && count($values) === 1) {
      return (bool) reset($values);
    }
    elseif (in_array($field->getType(), ['decimal']) && count($values) === 1) {
      return (float) reset($values);
    }
    elseif (count($values) == 1) {
      return $this->converter->convert((string) reset($values));
    }
    elseif (count($values) > 1) {

      // Some Vector Databases support arrays, return that in the metadata
      // and leave it to the Provider to flatten if needed.
      $parts = [];
      foreach ($values as $value) {
        if (in_array($field->getType(), ['date', 'boolean', 'integer'])) {
          $parts[] = (int) $this->converter->convert((string) $value);
        }
        else {
          $parts[] = $this->converter->convert((string) $value);
        }
      }
      return $parts;
    }
    return '';
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function deleteItems(IndexInterface $index, array $item_ids): void {
    [$provider_id, $model_id] = explode('__', $this->configuration['chat_model']);
    $index_name = $index->id();
    $this->getClient()->deleteHtml($item_ids, $index_name, $model_id);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function deleteAllIndexItems(IndexInterface $index, $datasource_id = NULL): void {
    [$provider_id, $model_id] = explode('__', $this->configuration['chat_model']);
    $index_name = $index->id();
    $this->getClient()->deleteIndex($index_name, $model_id);
  }

  /**
   * Set query results.
   *
   * @param \Drupal\search_api\Query\QueryInterface $query
   *   The query.
   *
   * @return void|null
   *   The results.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function search(QueryInterface $query) {
    // Check if we need to do entity access checks.
    $bypass_access = $query->getOption('search_api_bypass_access', FALSE);
    // Check if we have a custom value for the iterator.
    if ($query->getOption('search_api_ai_max_pager_iterations', 0)) {
      $this->maxAccessRetries = $query->getOption('search_api_ai_max_pager_iterations');
    }
    // Check if we should aggregate results.
    $get_chunked = $query->getOption('search_api_ai_get_chunks_result', FALSE);

    // Get index and ensure it is ready.
    if ($query->hasTag('server_index_status')) {
      return NULL;
    }
    $index = $query->getIndex();

    // Get DB Client.
    if (empty($this->configuration['database'])) {
      return NULL;
    }

    // Get query.
    $results = $query->getResults();

    // Prepare params.
    $params = [
      'collection_name' => $this->configuration['database_settings']['collection'],
      'output_fields' => ['id', 'drupal_entity_id', 'drupal_long_id', 'content'],
      // If an access check is in place, multiple iterations of the query are
      // run to attempt to reach this limit.
      'limit' => (int) $query->getOption('limit', 10),
      'offset' => (int) $query->getOption('offset', 0),
    ];

    if ($filters = $this->getClient()->prepareFilters($query)) {
      $params['filters'] = $filters;
    }

    // Conduct the search.
    $real_results = [];
    $meta_data = $this->doSearch($query, $params, $bypass_access, $real_results, $params['limit'], $params['offset']);
    // Keep track of items already added so existing result items do not get
    // overwritten by later records containing the same item.
    $stored_items = [];

    // Obtain results.
    foreach ($real_results as $match) {
      $id = $get_chunked ? $match['drupal_entity_id'] . ':' . $match['id'] : $match['drupal_entity_id'];
      $item = $this->getFieldsHelper()->createItem($index, $id);
      $item->setScore($match['distance'] ?? 1);
      $this->extractMetadata($match, $item);
      if (!$get_chunked && !in_array($item->getId(), $stored_items)) {
        $stored_items[] = $item->getId();
        $results->addResultItem($item);
      }
      else {
        $results->addResultItem($item);
      }
    }
    $results->setExtraData('real_offset', $meta_data['real_offset']);
    $results->setExtraData('reason_for_finish', $meta_data['reason']);
    // Get the last vector score.
    $results->setExtraData('current_vector_score', $meta_data['vector_score'] ?? 0);

    // Sort results.
    $sorts = $query->getSorts();
    if (!empty($sorts["search_api_relevance"])) {
      $result_items = $results->getResultItems();
      usort($result_items, function ($a, $b) use ($sorts) {
        $distance_a = $a->getScore();
        $distance_b = $b->getScore();
        return $sorts["search_api_relevance"] === 'DESC' ? $distance_b <=> $distance_a : $distance_a <=> $distance_b;
      });
      $results->setResultItems($result_items);
    }

    // Set results count.
    $results->setResultCount(count($results->getResultItems()));
  }

  /**
   * Run the search until enough items are found.
   */
  protected function doSearch(QueryInterface $query, $params, $bypass_access, &$results, $start_limit, $start_offset, $iteration = 0) {
    $params['database'] = $this->configuration['database_settings']['database_name'];
    $params['collection_name'] = $this->configuration['database_settings']['collection'];

    // Conduct the search.
    if (!$bypass_access) {
      // Double the results, if we need to run over access checks.
      $params['limit'] = $start_limit * 2;
      $params['offset'] = $start_offset + ($iteration * $start_limit * 2);
    }
    $search_words = $query->getKeys();
    if (!empty($search_words)) {
      [$provider_id, $model_id] = explode('__', $this->configuration['embeddings_engine']);
      $embedding_llm = $this->aiProviderManager->createInstance($provider_id);
      // We don't have to redo this.
      if (!isset($params['vector_input'])) {
        // Handle complex search queries, but we just normalize to string.
        // It makes no sense to do Boolean or other complex searches on vectors.
        if (is_array($search_words)) {
          if (isset($search_words['#conjunction'])) {
            unset($search_words['#conjunction']);
          }
          $search_words = implode(' ', $search_words);
        }
        $input = new EmbeddingsInput($search_words);
        $params['vector_input'] = $embedding_llm->embeddings($input, $model_id)->getNormalized();
      }
      $response = $this->getClient()->vectorSearch(...$params);
    }
    else {
      $response = $this->getClient()->querySearch(...$params);
    }

    // Obtain results.
    $i = 0;
    foreach ($response as $match) {
      $i++;
      // Do access checks.
      if (!$bypass_access && !$this->checkEntityAccess($match['drupal_entity_id'])) {
        // If we are not allowed to view this entity, we can skip it.
        continue;
      }
      // Passed.
      $results[] = $match;
      // If we found enough items, we can stop.
      if (count($results) == $start_limit) {
        return [
          'real_offset' => $start_offset + ($iteration * $start_limit * 2) + $i,
          'reason' => 'limit',
          'vector_score' => $match->distance ?? 0,
        ];
      }
    }

    // If we reach max retries, we can stop.
    if ($iteration == $this->maxAccessRetries) {
      return [
        'real_offset' => $iteration * $start_limit * 2 + $i,
        'reason' => 'max_retries',
        'vector_score' => $match->distance ?? 0,
      ];
    }
    // If we got less then limit back, it reached the end.
    if (count($response) < $start_limit) {
      return [
        'real_offset' => $iteration * $start_limit * 2 + $i,
        'reason' => 'reached_end',
        'vector_score' => $match->distance ?? 0,
      ];
    }
    // Else we need to continue.
    return $this->doSearch($query, $params, $bypass_access, $results, $start_limit, $start_offset, $iteration + 1);
  }

  /**
   * Extract query metadata values to a result item.
   *
   * @param array $result_row
   *   The result row.
   * @param \Drupal\search_api\Item\ItemInterface $item
   *   The item.
   */
  public function extractMetadata(array $result_row, ItemInterface $item): void {
    foreach ($result_row as $key => $value) {
      if ($key === 'vector' || $key === 'id' || $key === 'distance') {
        continue;
      }
      $item->setExtraData($key, $value);
    }
  }

  /**
   * Get the Client instance.
   *
   * @return \Drupal\ai\AiProviderInterface
   *   The Provider object.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function getClient(): object {
    $provider = explode('__', $this->configuration['chat_model'])[0];
    return $this->aiProviderManager->createInstance($provider);
  }

  /**
   * Check entity access.
   *
   * @param string $drupal_id
   *   The Drupal entity ID.
   *
   * @return bool
   *   If the entity is accessible.
   */
  private function checkEntityAccess(string $drupal_id): bool {
    [$entity_type, $id_lang] = explode('/', str_replace('entity:', '', $drupal_id));
    [$id, $lang] = explode(':', $id_lang);
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this->entityTypeManager->getStorage($entity_type)->load($id);

    // If the entity fails to load, assume false.
    if (!$entity instanceof EntityInterface) {
      return FALSE;
    }

    // Get the entity translation if a specific language is requested so long
    // as the entity is translatable in the first place.
    if (
      $entity instanceof TranslatableInterface
      && $entity->hasTranslation($lang)
    ) {
      $entity = $entity->getTranslation($lang);
    }
    return $entity->access('view', $this->currentUser);
  }

  /**
   * {@inheritdoc}
   */
  public function viewSettings(): array {
    $info = [];

    $provider = $this->getClient();
    $model = explode('__', $this->configuration['chat_model'])[1];

    $info[] = [
      'label' => $this->t('Provider'),
      'info' => $provider->getPluginDefinition()['label'],
    ];

    foreach ($provider->getConfiguredModels('ai_search_api') as $model_id => $label) {
      if ($model_id == $model) {
        $info[] = [
          'label' => $this->t('Model (Workspace)'),
          'info' => $label,
        ];
      }
    }

    return $info;
  }

}
