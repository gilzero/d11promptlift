<?php

declare(strict_types=1);

namespace Drupal\ui_skins\Theme;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\ui_skins\Definition\ThemeDefinition;

/**
 * Provides the default theme plugin manager.
 *
 * @method \Drupal\ui_skins\Definition\ThemeDefinition|null getDefinition($plugin_id, $exception_on_invalid = TRUE)
 * @method \Drupal\ui_skins\Definition\ThemeDefinition[] getDefinitions()
 */
class ThemePluginManager extends DefaultPluginManager implements ThemePluginManagerInterface {

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
  public function __construct(CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler) {
    $this->setCacheBackend($cache_backend, 'ui_skins_themes', ['ui_skins_themes']);
    $this->alterInfo('ui_skins_themes');
    $this->moduleHandler = $module_handler;
    $this->themeHandler = $theme_handler;

    // Set defaults in the constructor to be able to use string translation.
    $this->defaults = [
      'id' => '',
      'label' => '',
      'description' => '',
      'target' => 'body',
      'key' => 'class',
      'value' => '',
      'library' => '',
      'dependencies' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    $this->discovery = new YamlDiscovery('ui_skins.themes', $this->moduleHandler->getModuleDirectories() + $this->themeHandler->getThemeDirectories());
    $this->discovery->addTranslatableProperty('label', 'label_context');
    $this->discovery->addTranslatableProperty('description', 'description_context');
    $this->discovery = new ContainerDerivativeDiscoveryDecorator($this->discovery);
    return $this->discovery;
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
      throw new PluginException(\sprintf('Theme plugin property (%s) definition "id" is required.', $plugin_id));
    }

    $definition = new ThemeDefinition($definition);
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

    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitionWithDependencies(string $pluginId): array {
    $definitions = [];

    $definition = $this->getDefinition($pluginId, FALSE);
    if ($definition == NULL) {
      return $definitions;
    }

    // Handle dependencies.
    $dependencies = $definition->getDependencies();
    foreach ($dependencies as $dependency) {
      $definitions = \array_merge($definitions, $this->getDefinitionWithDependencies($dependency));
    }

    // Add our initial target last.
    $definitions[] = $definition;
    return $definitions;
  }

}
