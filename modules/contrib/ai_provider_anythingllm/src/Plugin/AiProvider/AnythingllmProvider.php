<?php

namespace Drupal\ai_provider_anythingllm\Plugin\AiProvider;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\ai\Attribute\AiProvider;
use Drupal\ai\Base\AiProviderClientBase;
use Drupal\ai_provider_anythingllm\AnythingllmChatMessageIterator;
use Drupal\ai_provider_anythingllm\OperationType\AiSearchApi\AiSearchApiInput;
use Drupal\ai_provider_anythingllm\OperationType\AiSearchApi\AiSearchApiInterface;
use Drupal\ai_provider_anythingllm\OperationType\AiSearchApi\AiSearchApiOutput;
use Drupal\ai\OperationType\Chat\ChatInput;
use Drupal\ai\OperationType\Chat\ChatInterface;
use Drupal\ai\OperationType\Chat\ChatMessage;
use Drupal\ai\OperationType\Chat\ChatOutput;
use Drupal\ai\OperationType\Embeddings\EmbeddingsInput;
use Drupal\ai\OperationType\Embeddings\EmbeddingsInterface;
use Drupal\ai\OperationType\Embeddings\EmbeddingsOutput;
use Drupal\ai\Traits\OperationType\ChatTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Plugin implementation of the 'anythingllm' provider.
 */
#[AiProvider(
  id: 'anythingllm',
  label: new TranslatableMarkup('AnythingLLM'),
)]
class AnythingllmProvider extends AiProviderClientBase implements
  ContainerFactoryPluginInterface,
  ChatInterface,
  EmbeddingsInterface {

  use StringTranslationTrait;
  use ChatTrait;

  /**
   * The Client for API calls.
   *
   * @var \Drupal\ai_provider_anythingllm\AnythingllmApi
   */
  protected $client;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * We want to add models to the provider dynamically.
   *
   * @var bool
   */
  protected bool $hasPredefinedModels = FALSE;

  /**
   * Dependency Injection for the Provider.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->currentUser = $container->get('current_user');
    $instance->messenger = $container->get('messenger');
    $instance->client = $container->get('ai_provider_anythingllm.api');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguredModels(?string $operation_type = NULL, array $capabilities = []): array {
    // Graceful failure.
    try {
      $response = $this->client->getModels();
    }
    catch (\Exception $e) {
      if ($this->currentUser->hasPermission('administer ai providers')) {
        $this->messenger->addError($this->t('Failed to get models from AnythingLLM: @error', ['@error' => $e->getMessage()]));
      }
      $this->loggerFactory->get('ai_provider_anythingllm')->error('Failed to get models from AnythingLLM: @error', ['@error' => $e->getMessage()]);
      return [];
    }

    $models = [];
    if (isset($response['workspaces'])) {
      foreach ($response['workspaces'] as $model) {
        $models[$model['slug']] = $model['name'];
      }
    }
    return $models;
  }

  /**
   * {@inheritdoc}
   */
  public function isUsable(?string $operation_type = NULL, array $capabilities = []): bool {
    // If its one of the bundles that this provider supports its usable.
    if (!$this->client->isApiSet()) {
      return FALSE;
    }
    if ($operation_type) {
      return in_array($operation_type, $this->getSupportedOperationTypes());
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getSupportedOperationTypes(): array {
    return [
      'chat',
      'embeddings',
      'ai_search_api',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(): ImmutableConfig {
    return $this->configFactory->get('ai_provider_anythingllm.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function getApiDefinition(): array {
    // Load the configuration.
    $definition = Yaml::parseFile($this->moduleHandler->getModule('ai_provider_anythingllm')->getPath() . '/definitions/api_defaults.yml');
    return $definition;
  }

  /**
   * {@inheritdoc}
   */
  public function getModelSettings(string $model_id, array $generalConfig = []): array {
    return $generalConfig;
  }

  /**
   * {@inheritdoc}
   */
  public function maxEmbeddingsInput($model_id = ''): int {
    // @todo Safe value, we cannot get value from API.
    return 1024;
  }

  /**
   * {@inheritdoc}
   */
  public function setAuthentication(mixed $authentication): void {
    $this->client->setApiKey($authentication['api_key'] ?? '');
    $this->client->setApiUrl($authentication['api_url'] ?? '');
  }

  /**
   * {@inheritdoc}
   */
  public function chat(array|string|ChatInput $input, string $model_id, array $tags = []): ChatOutput {

    // Get Config for model.
    $config = $this->configFactory->get('ai.settings')->get('models');
    $model_config = $config['anythingllm']['chat'][$model_id] ?? [];

    // Normalize the input if needed.
    $chat_input = $input;
    if ($input instanceof ChatInput) {
      $chat_input = [];
      // Add a system role if wanted.
      if ($this->chatSystemRole) {
        $chat_input[] = [
          'role' => 'system',
          'content' => $this->chatSystemRole,
        ];
      }
      /** @var \Drupal\ai\OperationType\Chat\ChatMessage $message */
      foreach ($input->getMessages() as $message) {

        $images = [];
        if (count($message->getImages())) {
          foreach ($message->getImages() as $image) {
            $array = [
              'name' => $image->getFilename(),
              'mime' => $image->getMimeType(),
              'contentString' => 'data:' . $image->getMimeType() . ';base64,' . $image->getAsBase64EncodedString(''),
            ];
            $images[] = $array;
          }
        }
        $new_message = [
          'role' => $message->getRole(),
          'content' => $message->getText(),
          'images' => $images,
        ];
        $chat_input[] = $new_message;
      }
    }

    // See #3497002 - remove generation later.
    if (!empty($this->sessionId)) {
      $sessionId = $this->sessionId;
    }
    else {
      $sessionId = Crypt::hashBase64(uniqid(__CLASS__, TRUE) . microtime(TRUE));
    }

    $payload = [
      'model' => $model_id,
      'messages' => $chat_input,
      'stream' => $this->streamed,
      'temperature' => 0.7,
      'session_id' => $sessionId,
    ] + $this->configuration;

    $response = $this->client->chat($payload, $model_config);

    if ($this->streamed) {
      $response = new \ArrayObject([$response]);
      $message = new AnythingllmChatMessageIterator($response);
    }
    else {
      $message = new ChatMessage($response['choices'][0]['message']['role'], $response['choices'][0]['message']['content']);
    }

    $chat = new ChatOutput($message, $response, []);

    return $chat;
  }

  /**
   * {@inheritdoc}
   */
  public function embeddings(string|EmbeddingsInput $input, string $model_id, array $tags = []): EmbeddingsOutput {
    // Normalize the input if needed.
    if ($input instanceof EmbeddingsInput) {
      $input = $input->getPrompt();
    }
    $payload = [
      'inputs' => [
        $input,
      ],
      'model' => $model_id,
    ];
    $response = $this->client->embeddings($payload);
    return new EmbeddingsOutput($response['data'][0]['embedding'], $response, []);
  }

  /**
   * {@inheritdoc}
   */
  public function loadModelsForm(array $form, $form_state, string $operation_type, string|NULL $model_id = NULL): array {
    $form = parent::loadModelsForm($form, $form_state, $operation_type, $model_id);
    $config = $this->loadModelConfig($operation_type, $model_id);

    if ($operation_type == 'chat') {
      $form['model_data']['chat_endpoint'] = [
        '#type' => 'radios',
        '#options' => [
          'anythingllm' => $this->t('Anything LLM'),
          'openai' => $this->t('OpenAI compatibility mode'),
        ],
        '#title' => $this->t('Chat Endpoint'),
        '#description' => $this->t('<strong>Anything LLM</strong> has no history but supports images/files.<br><strong>OpenAI compatibility mode</strong> supports history but no images/files.'),
        '#default_value' => $config['chat_endpoint'] ?? 'openai',
        '#required' => TRUE,
        '#weight' => -10,
      ];
    }
    return $form;
  }

  /**
   * Store raw document.
   *
   * @param string $content
   *   Raw content of the document.
   * @param array $meta
   *   Metadata of the document.
   * @param string $index_name
   *   The machine name of search API index.
   * @param string $model_id
   *   The model_id (aka workspace) for AnythingLLM.
   *
   * @return string
   *   Filename of stored data document.
   */
  public function storeRaw($content, $meta, $index_name, $model_id): string {
    // Generate index folder.
    $folder_name = 'drupal_search_api_' . $index_name;
    $response = $this->createFolder($folder_name);

    // Generate payload and store data.
    $payload = [
      'textContent' => $content,
      'metadata' => $meta,
    ];
    $response = $this->client->rawText($payload);

    // Move document to index folder.
    $file = $response['documents'][0]['location'];
    $target = $folder_name . '/' . $index_name . '--' . $this->getMachineName($meta['docSource']) . '.json';
    $response = $this->moveFile($file, $target);

    // Create embed from stored document.
    $response = $this->updateEmbedding($target, $model_id);

    return $target;
  }

  /**
   * Delete documents.
   *
   * @param array $item_ids
   *   Array of search API item IDs.
   * @param string $index_name
   *   The machine name of search API index.
   * @param string $model_id
   *   The model_id (aka workspace) for AnythingLLM.
   */
  public function deleteHtml($item_ids, $index_name, $model_id): void {
    // Generate name of index folder.
    $folder_name = 'drupal_search_api_' . $index_name;

    // Iterate item_ids and generate filenames.
    $files = [];
    foreach ($item_ids as $id) {
      $files[] = $folder_name . '/' . $index_name . '--' . $this->getMachineName($id) . '.json';
    }

    // Delete Embeddings.
    $this->deleteEmbeddings($files, $model_id);

    // Delete documents.
    $this->removeFiles($files);
  }

  /**
   * Delete index (all documents).
   *
   * @param string $index_name
   *   The machine name of search API index.
   * @param string $model_id
   *   The model_id (aka workspace) for AnythingLLM.
   */
  public function deleteIndex($index_name, $model_id): void {
    // Generate name of index folder.
    $folder_name = 'drupal_search_api_' . $index_name;

    // Get all documents and filter for folder.
    $files = [];
    $docs = $this->getDocuments();
    foreach ($docs['localFiles']['items'] as $doc) {
      if (($doc['name'] == $folder_name) && ($doc['type'] == 'folder')) {
        foreach ($doc['items'] as $item) {
          $files[] = $folder_name . '/' . $item['name'];
        }
      }
    }

    // Delete Embeddings.
    $this->deleteEmbeddings($files, $model_id);

    // Delete documents.
    $this->removeFiles($files);
  }

  /**
   * Update (create) single Embedding.
   *
   * @param string $file
   *   Name of the document to embed.
   * @param string $model_id
   *   The model_id (aka workspace) for AnythingLLM.
   *
   * @return array
   *   API response.
   */
  public function updateEmbedding($file, $model_id): array {
    $payload = [
      'model_id' => $model_id,
      'data' => [
        'adds' => [
          $file,
        ],
      ],
    ];
    return $this->client->updateEmbeddings($payload);
  }

  /**
   * Update (create) Embeddings.
   *
   * @param array $files
   *   Names of the documents to embed.
   * @param string $model_id
   *   The model_id (aka workspace) for AnythingLLM.
   *
   * @return array
   *   API response.
   */
  public function updateEmbeddings($files, $model_id): array {
    $payload = [
      'model_id' => $model_id,
      'data' => [
        'adds' => $files,
      ],
    ];
    return $this->client->updateEmbeddings($payload);
  }

  /**
   * Delete single Embedding.
   *
   * @param string $file
   *   Name of the document to delete from embeddings.
   * @param string $model_id
   *   The model_id (aka workspace) for AnythingLLM.
   *
   * @return array
   *   API response.
   */
  public function deleteEmbedding($file, $model_id): array {
    $payload = [
      'model_id' => $model_id,
      'data' => [
        'deletes' => [
          $file,
        ],
      ],
    ];
    return $this->client->updateEmbeddings($payload);
  }

  /**
   * Delete Embeddings.
   *
   * @param array $files
   *   Names of the documents to delete from embeddings.
   * @param string $model_id
   *   The model_id (aka workspace) for AnythingLLM.
   *
   * @return array
   *   API response.
   */
  public function deleteEmbeddings($files, $model_id): array {
    $payload = [
      'model_id' => $model_id,
      'data' => [
        'deletes' => $files,
      ],
    ];
    return $this->client->updateEmbeddings($payload);
  }

  /**
   * Create folder for documents.
   *
   * @param string $folder
   *   Name of the folder to create.
   *
   * @return array|null
   *   API response.
   */
  public function createFolder($folder): array|null {
    $payload = [
      'name' => $folder,
    ];
    return $this->client->createFolder($payload);
  }

  /**
   * Move (rename) single file.
   *
   * @param string $src
   *   Folder and name of source file.
   * @param string $dest
   *   Folder and name of destination.
   *
   * @return array
   *   API response.
   */
  public function moveFile($src, $dest): array {
    $payload = [
      'files' => [
        [
          'from' => $src,
          'to' => $dest,
        ],
      ],
    ];
    return $this->client->moveFile($payload);
  }

  /**
   * Delete single file.
   *
   * @param string $file
   *   Name of the file to delete.
   *
   * @return array
   *   API response.
   */
  public function removeFile($file): array {
    $payload = [
      'names' => [
        $file,
      ],
    ];
    return $this->client->removeDocuments($payload);
  }

  /**
   * Delete files.
   *
   * @param array $files
   *   Name of the files to delete.
   *
   * @return array
   *   API response.
   */
  public function removeFiles($files): array {
    $payload = [
      'names' => $files,
    ];
    return $this->client->removeDocuments($payload);
  }

  /**
   * Get all files (documents).
   *
   * @return array
   *   Nested array of documents.
   */
  public function getDocuments(): array {
    return $this->client->getDocuments();
  }

  /**
   * Generates a machine name from a string.
   *
   * This is basically the same as what is done in
   * \Drupal\Core\Block\BlockBase::getMachineNameSuggestion() and
   * \Drupal\system\MachineNameController::transliterate(), but it seems
   * that so far there is no common service for handling this.
   *
   * @param string $string
   *   String to have translated.
   *
   * @return string
   *   The machine name.
   *
   * @see \Drupal\Core\Block\BlockBase::getMachineNameSuggestion()
   * @see \Drupal\system\MachineNameController::transliterate()
   */
  protected function getMachineName($string): string {
    $transliterated = \Drupal::transliteration()->transliterate($string, LanguageInterface::LANGCODE_DEFAULT, '_');
    $transliterated = mb_strtolower($transliterated);

    $transliterated = preg_replace('@[^a-z0-9_.]+@', '_', $transliterated);

    return $transliterated;
  }

}
