<?php

declare(strict_types=1);

namespace Drupal\ui_skins\CssVariable;

use Drupal\Component\Plugin\CategorizingPluginManagerInterface;
use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Defines an interface for Css variable plugin managers.
 */
interface CssVariablePluginManagerInterface extends PluginManagerInterface, CategorizingPluginManagerInterface {

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\ui_skins\Definition\CssVariableDefinition|null
   *   The plugin definition. NULL if not found.
   *
   * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
   */
  public function getDefinition($plugin_id, $exception_on_invalid = TRUE);

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\ui_skins\Definition\CssVariableDefinition[]
   *   The plugins definitions.
   */
  public function getDefinitions();

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\ui_skins\Definition\CssVariableDefinition[]|null $definitions
   *   (optional) The plugin definitions to sort. If omitted, all plugin
   *   definitions are used.
   *
   * @return \Drupal\ui_skins\Definition\CssVariableDefinition[]
   *   The sorted definitions.
   *
   * @phpstan-ignore-next-line
   */
  public function getSortedDefinitions(?array $definitions = NULL): array;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\ui_skins\Definition\CssVariableDefinition[]|null $definitions
   *   (optional) The plugin definitions to group. If omitted, all plugin
   *   definitions are used.
   *
   * @return \Drupal\ui_skins\Definition\CssVariableDefinition[][]
   *   The sorted definitions grouped by category.
   */
  public function getGroupedDefinitions(?array $definitions = NULL): array;

  /**
   * Filter plugins by the modules and the selected theme and its parents.
   *
   * @param string $theme
   *   The theme to filter the plugins against.
   *
   * @return \Drupal\ui_skins\Definition\CssVariableDefinition[][]
   *   The list of filtered, grouped and sorted definitions.
   */
  public function getDefinitionsForTheme(string $theme): array;

}
