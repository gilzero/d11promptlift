<?php

declare(strict_types=1);

namespace Drupal\ui_skins\CssVariable;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\ui_skins\Definition\CssVariableDefinition;

/**
 * Provides the default variable plugin manager.
 *
 * @method \Drupal\ui_skins\Definition\CssVariableDefinition|null getDefinition($plugin_id, $exception_on_invalid = TRUE)
 * @method \Drupal\ui_skins\Definition\CssVariableDefinition[] getDefinitions()
 */
class CssVariablePluginManager extends DefaultPluginManager implements CssVariablePluginManagerInterface {

  use StringTranslationTrait;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected ThemeHandlerInterface $themeHandler;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   */
  public function __construct(
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler,
    ThemeHandlerInterface $theme_handler,
  ) {
    $this->setCacheBackend($cache_backend, 'ui_skins_css_variables', ['ui_skins_css_variables']);
    $this->alterInfo('ui_skins_css_variables');
    $this->moduleHandler = $module_handler;
    $this->themeHandler = $theme_handler;

    // Set defaults in the constructor to be able to use string translation.
    $this->defaults = [
      'id' => '',
      'enabled' => TRUE,
      'type' => 'textfield',
      'label' => '',
      'description' => '',
      'category' => $this->t('Other'),
      'default_values' => [],
      'weight' => 0,
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    $this->discovery = new YamlDiscovery('ui_skins.css_variables', $this->moduleHandler->getModuleDirectories() + $this->themeHandler->getThemeDirectories());
    $this->discovery->addTranslatableProperty('label', 'label_context');
    $this->discovery->addTranslatableProperty('description', 'description_context');
    $this->discovery->addTranslatableProperty('category', 'category_context');
    $this->discovery = new ContainerDerivativeDiscoveryDecorator($this->discovery);
    return $this->discovery;
  }

  /**
   * {@inheritdoc}
   */
  public function getCategories() {
    // Fetch all categories from definitions and remove duplicates.
    $categories = \array_unique(\array_values(\array_map(static function (CssVariableDefinition $definition) {
      return $definition->getCategory();
    }, $this->getDefinitions())));
    \natcasesort($categories);
    // @phpstan-ignore-next-line
    return $categories;
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-ignore-next-line
   */
  public function getSortedDefinitions(?array $definitions = NULL): array {
    $definitions = $definitions ?? $this->getDefinitions();

    \uasort($definitions, static function (CssVariableDefinition $item1, CssVariableDefinition $item2) {
      // Sort by weight.
      $weight = $item1->getWeight() <=> $item2->getWeight();
      if ($weight != 0) {
        return $weight;
      }

      // Sort by category.
      $category1 = $item1->getCategory();
      if ($category1 instanceof TranslatableMarkup) {
        $category1 = $category1->render();
      }
      $category2 = $item2->getCategory();
      if ($category2 instanceof TranslatableMarkup) {
        $category2 = $category2->render();
      }
      if ($category1 != $category2) {
        return \strnatcasecmp($category1, $category2);
      }

      // Sort by label ignoring parenthesis.
      $label1 = $item1->getLabel();
      if ($label1 instanceof TranslatableMarkup) {
        $label1 = $label1->render();
      }
      $label2 = $item2->getLabel();
      if ($label2 instanceof TranslatableMarkup) {
        $label2 = $label2->render();
      }
      // Ignore parenthesis.
      $label1 = \str_replace(['(', ')'], '', $label1);
      $label2 = \str_replace(['(', ')'], '', $label2);
      if ($label1 != $label2) {
        return \strnatcasecmp($label1, $label2);
      }

      // Sort by plugin ID.
      // In case the plugin ID starts with an underscore.
      $id1 = \str_replace('_', '', $item1->id());
      $id2 = \str_replace('_', '', $item2->id());
      return \strnatcasecmp($id1, $id2);
    });

    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupedDefinitions(?array $definitions = NULL): array {
    $definitions = $this->getSortedDefinitions($definitions ?? $this->getDefinitions());
    $grouped_definitions = [];
    foreach ($definitions as $id => $definition) {
      $grouped_definitions[(string) $definition->getCategory()][$id] = $definition;
    }
    return $grouped_definitions;
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-ignore-next-line
   */
  protected function alterDefinitions(&$definitions) {
    /** @var \Drupal\ui_skins\Definition\CssVariableDefinition[] $definitions */
    foreach ($definitions as $definition_key => $definition) {
      if (!$definition->isEnabled()) {
        unset($definitions[$definition_key]);
      }
    }

    parent::alterDefinitions($definitions);
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-ignore-next-line
   */
  public function processDefinition(&$definition, $plugin_id): void {
    // Call parent first to set defaults while still manipulating an array.
    // Otherwise, as there is currently no derivative system among CSS variable
    // plugins, there is no deriver or class attributes.
    parent::processDefinition($definition, $plugin_id);

    if (empty($definition['id'])) {
      throw new PluginException(\sprintf('Css variable plugin property (%s) definition "id" is required.', $plugin_id));
    }

    $definition = new CssVariableDefinition($definition);
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-ignore-next-line
   */
  protected function providerExists($provider): bool {
    return $this->moduleHandler->moduleExists($provider) || $this->themeHandler->themeExists($provider);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitionsForTheme(string $theme): array {
    $themes = $this->themeHandler->listInfo();
    // Create a list which includes the current theme and all its base themes.
    if (isset($themes[$theme]->base_themes)) {
      $theme_keys = \array_keys($themes[$theme]->base_themes);
      $theme_keys[] = $theme;
    }
    else {
      $theme_keys = [$theme];
    }

    $definitions = $this->getDefinitions();
    foreach ($definitions as $definition_key => $definition) {
      if ($this->moduleHandler->moduleExists($definition->getProvider())
        || \in_array($definition->getProvider(), $theme_keys, TRUE)) {
        continue;
      }

      unset($definitions[$definition_key]);
    }

    return $this->getGroupedDefinitions($definitions);
  }

}
