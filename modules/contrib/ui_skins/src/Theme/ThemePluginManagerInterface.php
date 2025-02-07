<?php

declare(strict_types=1);

namespace Drupal\ui_skins\Theme;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Defines an interface for theme plugin managers.
 */
interface ThemePluginManagerInterface extends PluginManagerInterface {

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\ui_skins\Definition\ThemeDefinition|null
   *   The plugin definition. NULL if not found.
   *
   * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
   */
  public function getDefinition($plugin_id, $exception_on_invalid = TRUE);

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\ui_skins\Definition\ThemeDefinition[]
   *   The plugins definitions.
   */
  public function getDefinitions();

  /**
   * Filter plugins by the modules and the selected theme and its parents.
   *
   * @param string $theme
   *   The theme to filter the plugins against.
   *
   * @return \Drupal\ui_skins\Definition\ThemeDefinition[]
   *   The list of filtered, grouped and sorted definitions.
   */
  public function getDefinitionsForTheme(string $theme): array;

  /**
   * Get the plugin definition with its dependencies.
   *
   * @param string $pluginId
   *   A plugin id.
   *
   * @return \Drupal\ui_skins\Definition\ThemeDefinition[]
   *   The plugin definitions.
   */
  public function getDefinitionWithDependencies(string $pluginId): array;

}
