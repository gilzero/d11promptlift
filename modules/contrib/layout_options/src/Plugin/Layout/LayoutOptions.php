<?php

declare(strict_types=1);

namespace Drupal\layout_options\Plugin\Layout;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\SortArray;
use Drupal\Core\Discovery\YamlDiscovery;
use Drupal\Core\Entity\ContentEntityFormInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Utility\Error;
use Drupal\layout_options\LayoutOptionPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Layout Plugin that allows format options to be defined via YAML files.
 */
class LayoutOptions extends LayoutDefault implements PluginFormInterface, ContainerFactoryPluginInterface {

  use MessengerTrait;
  use LoggerChannelTrait;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * The LayoutOptionPlugin service.
   *
   * @var \Drupal\layout_options\LayoutOptionPluginManager
   */
  protected $layoutOptionManager;

  /**
   * The YAML discovery class to find all .layout_options.yml files.
   *
   * @var \Drupal\Core\Discovery\YamlDiscovery
   */
  protected $yamlDiscovery;

  /**
   * The loaded and combined YAML file information.
   *
   * @var string[]
   */
  protected $layoutOptionsSchema;

  /**
   * The option plug local cache.
   *
   * @var \Drupal\layout_options\OptionInterface[]
   */
  protected $optionPlugins = [];

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler object.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $themeHandler
   *   The theme handler object.
   * @param \Drupal\layout_options\LayoutOptionPluginManager $layoutOptionManager
   *   The theme handler object.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ModuleHandlerInterface $moduleHandler,
    ThemeHandlerInterface $themeHandler,
    LayoutOptionPluginManager $layoutOptionManager,
  ) {
    $this->moduleHandler = $moduleHandler;
    $this->themeHandler = $themeHandler;
    $this->layoutOptionManager = $layoutOptionManager;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    // @phpstan-ignore-next-line
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler'),
      $container->get('theme_handler'),
      $container->get('plugin.manager.layout_options')
    );
  }

  /**
   * {@inheritdoc}
   *
   * Note: Defaults cannot be set for field level options.
   */
  public function defaultConfiguration() {
    $configuration = parent::defaultConfiguration();
    $options = $this->parseLayoutOptions($this->getPluginDefinition()->id());
    $keys = \array_keys($options);
    foreach ($keys as $optionId) {
      $optionDef = $this->getLayoutDefinition($optionId, $options);
      $plugin = $this->getOptionPlugin($optionId, $optionDef);
      if ($plugin !== NULL) {
        $configuration = $plugin->addDefaults($configuration);
      }
    }
    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $configuration = $this->getConfiguration();
    $field = $configuration['field_name'] ?? NULL;
    $build = parent::build($regions);
    $defs = $this->parseLayoutOptions($this->getPluginDefinition()->id(), $field);

    $optionIds = \array_keys($defs);
    foreach ($optionIds as $optionId) {
      $optionDef = $this->getLayoutDefinition($optionId, $defs);
      $plugin = $this->getOptionPlugin($optionId, $optionDef);
      if ($plugin !== NULL) {
        $build = $plugin->buildOption($regions, $build);
      }
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   *
   * Add a process callback so $form[#parents] are correctly populated.
   *
   * If we were adding options that did not depend on
   * third party widget settings, using #process would be unnecessary.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    if (\method_exists(\get_parent_class($this), 'buildConfigurationForm')) {
      $form = parent::buildConfigurationForm($form, $form_state);
    }
    $form['#process'][] = [$this, 'processConfigurationForm'];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function processConfigurationForm(array $form, FormStateInterface $form_state): array {
    // If ERL then pass on the field name so it can be used elsewhere.
    $field = NULL;
    $form_object = $form_state->getFormObject();
    if ($form_object instanceof ContentEntityFormInterface) {
      $field = $form['#parents'][0];
      $form['field_name'] = [
        '#type' => 'value',
        '#value' => $field,
      ];
    }
    $defs = $this->parseLayoutOptions($this->getPluginDefinition()->id(), $field);
    $keys = \array_keys($defs);
    // Layout.
    $form['layout'] = [];
    foreach ($keys as $optionId) {
      $optionDef = $this->getLayoutDefinition($optionId, $defs);
      $plugin = $this->getOptionPlugin($optionId, $optionDef);
      if ($plugin) {
        $form = $plugin->addOptionFormElement('layout', $form, $form_state);
      }
    }
    // Remove empty layout.
    if (empty($form['layout'])) {
      unset($form['layout']);
    }
    else {
      $form['layout']['#type'] = 'details';
      $form['layout']['#title'] = $this->t('Layout');
    }

    // Regions.
    foreach ($this->getPluginDefinition()->getRegions() as $region => $regionInfo) {
      $form[$region] = [];
      foreach ($keys as $optionId) {
        $optionDef = $this->getLayoutDefinition($optionId, $defs);
        $plugin = $this->getOptionPlugin($optionId, $optionDef);
        if ($plugin) {
          $form = $plugin->addOptionFormElement($region, $form, $form_state);
        }
      }
      // Remove empty regions.
      if (empty($form[$region])) {
        unset($form[$region]);
      }
      else {
        $regionLabel = $regionInfo['label'];
        $form[$region]['#type'] = 'details';
        $form[$region]['#title'] = $this->t('@region region', ['@region' => $regionLabel]);
      }
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state): void {
    if (\method_exists(\get_parent_class($this), 'validateConfigurationForm')) {
      parent::validateConfigurationForm($form, $form_state);
    }
    $field = NULL;
    if ($form_state instanceof SubformStateInterface) {
      $compFormState = $form_state->getCompleteFormState();
      /** @var string $field */
      $field = $compFormState->getValue('field_name');
    }

    $options = $this->parseLayoutOptions($this->getPluginDefinition()->id(), $field);
    $keys = \array_keys($options);
    foreach ($keys as $optionId) {
      $optionDef = $this->getLayoutDefinition($optionId, $options);
      $plugin = $this->getOptionPlugin($optionId, $optionDef);
      if ($plugin !== NULL) {
        $plugin->validateFormOption($form, $form_state);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    if (\method_exists(\get_parent_class($this), 'submitConfigurationForm')) {
      parent::submitConfigurationForm($form, $form_state);
    }
    $configuration = $this->getConfiguration();

    $field = NULL;
    if ($form_state->hasValue('field_name')) {
      /** @var string $field */
      $field = $form_state->getValue('field_name');
      $configuration['field_name'] = $field;
    }
    $options = $this->parseLayoutOptions($this->getPluginDefinition()->id(), $field);

    $keys = \array_keys($options);
    foreach ($keys as $optionId) {
      $optionDef = $this->getLayoutDefinition($optionId, $options);
      $plugin = $this->getOptionPlugin($optionId, $optionDef);
      if ($plugin !== NULL) {
        $configuration = $plugin->submitFormOption($configuration, $form, $form_state);
      }
    }
    $this->setConfiguration($configuration);
  }

  /**
   * Parse the layout rules to determine options to use in this context.
   *
   * Note:  The field name may not be available unless options are chosen in
   *        the layout configuration form.
   *
   * @param string|null $layoutId
   *   The id of the layout being used.
   * @param string|null $fieldName
   *   (optional) The field that contains this layout.
   *
   * @return array
   *   The option definitions that apply to this context.
   */
  public function parseLayoutOptions(?string $layoutId = NULL, ?string $fieldName = NULL) {
    $rules = $this->getLayoutOptions();
    $options = $rules['global'] ?? [];
    if ($layoutId && isset($rules[$layoutId])) {
      $options = NestedArray::mergeDeep($options, $rules[$layoutId]);
    }
    if ($fieldName && isset($rules[$fieldName])) {
      $options = NestedArray::mergeDeep($options, $rules[$fieldName]);
    }

    // Merge definition with rules keeping rule overrides.
    $option_definitions = $this->getLayoutDefinitions();
    foreach ($option_definitions as $option => $config) {
      if (isset($options[$option])) {
        $definition = [];
        $definition[$option] = $config;
        $options = NestedArray::mergeDeep($definition, $options);
      }
    }

    // Sort by weight.
    \uasort($options, [SortArray::class, 'sortByWeightElement']);

    return $options;
  }

  /**
   * Gets the layout options 'rules'.
   *
   * @return array
   *   The layout option array or an empty array if none found.
   */
  public function getLayoutOptions() {
    $schema = $this->getLayoutOptionsSchema();
    return $schema['layout_options'] ?? [];
  }

  /**
   * Gets all the layout option definitions.
   *
   * NOTE: This is the default definitions not parsed with any rules.  Use
   * parseLayoutOptions() to get the context specific definitions.
   *
   * @return array
   *   The layout option definitions keyed by option id or an empty array
   *   if not found.
   */
  public function getLayoutDefinitions() {
    $schema = $this->getLayoutOptionsSchema();
    return $schema['layout_option_definitions'] ?? [];
  }

  /**
   * Gets a specific layout option definition.
   *
   * @param string $id
   *   The option id to lookup.
   * @param array $defs
   *   The context specific definitions to use (e.g from parseLayoutOptions).
   *
   * @return string[]
   *   The definition array or an empty array if not found.
   */
  public function getLayoutDefinition($id, array $defs) {
    return $defs[$id] ?? [];
  }

  /**
   * Gets the layout options scheme defined in the layout_options.yml files.
   *
   * This is a merge of all the yaml files with the last loaded taking
   * precedence. The order is based on Drupal's module load order followed by
   * the theme load order.
   *
   * @return array
   *   The layout options scheme or an empty array if no files found.
   */
  public function getLayoutOptionsSchema() {
    if ($this->layoutOptionsSchema != NULL) {
      return $this->layoutOptionsSchema;
    }

    try {
      $results = $this->getYamlDiscovery()->findAll();
    }
    catch (\Exception $e) {
      $this->messenger()->addError($this->t('Error reading layout_options.yml files.  See watchdog log for details'));
      $variables = Error::decodeException($e);
      $this->getLogger('layout_options')->error('%type: @message in %function (line %line of %file).', $variables);
      return [];
    }

    $results = $this->filterAndSortResultsByThemeHierarchy($results);

    $layoutOptionsSchema = [];
    foreach ($results as $config) {
      $layoutOptionsSchema = NestedArray::mergeDeep($layoutOptionsSchema, $config);
    }
    // Warnings only.
    $this->validateDefinitions($layoutOptionsSchema);
    $this->layoutOptionsSchema = $layoutOptionsSchema;
    return $this->layoutOptionsSchema;
  }

  /**
   * Validate the layout option definitions returned via Discovery.
   *
   * @param array $schema
   *   The schema to validate.
   *
   * @return bool
   *   Return true if valid / false if not
   */
  public function validateDefinitions(array $schema) {
    $hasProblems = FALSE;
    $definitions = $schema['layout_option_definitions'] ?? [];
    foreach ($definitions as $option => $definition) {
      if (!\is_array($definition)) {
        $hasProblems = TRUE;
        $this->messenger()->addError($this->t("Layout option definition, '@option', has invalid declaration.", [
          '@option' => $option,
        ]));
        continue;
      }
      $plugin = $this->getOptionPlugin($option, $definition);
      if ($plugin == NULL) {
        $hasProblems = TRUE;
        $this->messenger()->addError($this->t("Layout option definition, '@option', has an invalid plugin id.", [
          '@option' => $option,
        ]));
        continue;
      }
      $problems = $plugin->validateOptionDefinition($definition);
      if (!empty($problems)) {
        $hasProblems = TRUE;
        $this->messenger()->addError($this->t("Layout option definition, '@option', has these problems: @problems.", [
          '@option' => $option,
          '@problems' => $problems,
        ]));
      }
    }
    return !$hasProblems;
  }

  /**
   * Gets the YAML discovery object used to load the layout_options yaml files.
   *
   * @return \Drupal\Core\Discovery\YamlDiscovery
   *   The YAML discovery object.
   */
  public function getYamlDiscovery() {
    if ($this->yamlDiscovery == NULL) {
      $this->yamlDiscovery = new YamlDiscovery(
        'layout_options',
        $this->moduleHandler->getModuleDirectories() +
        $this->themeHandler->getThemeDirectories()
      );
    }
    return $this->yamlDiscovery;
  }

  /**
   * Loads and configure the plugin defined by the specified option definition.
   *
   * Note: If a plugin is not specified or is not valid, a watchdog
   * warning is logged.
   *
   * @param string $optionId
   *   The definition option id.
   * @param array $optionDefinition
   *   The array that defines this option's definition.
   * @param bool $bypassCache
   *   Option to allow bypassing cached plugin info.
   *
   * @return \Drupal\layout_options\OptionInterface|null
   *   The plugin or NULL in not found.
   *
   * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
   */
  public function getOptionPlugin(string $optionId, array $optionDefinition, bool $bypassCache = FALSE) {
    if (!isset($this->optionPlugins[$optionId]) || $bypassCache) {
      if (!isset($optionDefinition['plugin'])) {
        $this->messenger()->addError($this->t('Option definition, @option (@title), does not define a plugin id', [
          '@option' => $optionId,
          '@title' => $optionDefinition['title'] ?? '',
        ]));
        $this->getLogger('layout_options')->warning('Option definition, @option (@title), does not define a plugin id', [
          '@option' => $optionId,
          '@title' => $optionDefinition['title'] ?? '',
        ]);
        return NULL;
      }
      $plugin_id = $optionDefinition['plugin'];
      $conf = [
        'option_id' => $optionId,
        'definition' => $optionDefinition,
        'layout_plugin' => $this,
      ];
      try {
        /** @var \Drupal\layout_options\OptionInterface $plugin */
        $plugin = $this->layoutOptionManager->createInstance($plugin_id, $conf);
      }
      catch (PluginNotFoundException $e) {
        $this->messenger()->addError($this->t('Plugin, "%plugin", not found.  See watchdog log for details', [
          '%plugin' => $plugin_id,
        ]));
        $variables = Error::decodeException($e);
        $this->getLogger('layout_options')->error('%type: @message in %function (line %line of %file).', $variables);
        return NULL;
      }
      $this->optionPlugins[$optionId] = $plugin;
    }
    /** @var \Drupal\layout_options\OptionInterface $plugin */
    $plugin = $this->optionPlugins[$optionId];
    // Update definition in config because it can change depending on context.
    $configuration = $plugin->getConfiguration();
    $configuration['definition'] = $optionDefinition;
    $plugin->setConfiguration($configuration);
    return $plugin;
  }

  /**
   * Clears the plugin cache and forces getOptionPlugin to reload plugins.
   */
  public function clearPluginCache(): void {
    $this->optionPlugins = [];
  }

  /**
   * Ensure theme hierarchy is respected in the results.
   *
   * Remove themes that are not relevant for the default theme.
   *
   * We discover layout options in modules and themes, but we intentionally
   * prefer theme overrides, especially the active theme, over modules. It is
   * considered that themes must always prevail in defining output behavior.
   *
   * @param array $results
   *   The results from the YAML discovery.
   *
   * @return array
   *   The sorted results.
   */
  protected function filterAndSortResultsByThemeHierarchy(array $results): array {
    $default_theme_name = $this->themeHandler->getDefault();
    $themes = $this->themeHandler->listInfo();
    // Create a list which includes the current theme and all its base themes.
    if (isset($themes[$default_theme_name]->base_themes)) {
      $theme_keys = \array_keys($themes[$default_theme_name]->base_themes);
      $theme_keys[] = $default_theme_name;
    }
    else {
      $theme_keys = [$default_theme_name];
    }

    // Set default theme hierarchy last.
    foreach ($theme_keys as $theme_name) {
      if (isset($results[$theme_name])) {
        $info = $results[$theme_name];
        unset($results[$theme_name]);
        $results[$theme_name] = $info;
      }
    }

    // Remove non-relevant themes.
    foreach ($themes as $theme) {
      $theme_name = $theme->getName();
      if (\in_array($theme_name, $theme_keys, TRUE)) {
        continue;
      }

      unset($results[$theme_name]);
    }

    return $results;
  }

}
