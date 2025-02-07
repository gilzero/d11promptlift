<?php

declare(strict_types=1);

namespace Drupal\ui_skins_test;

use Drupal\ui_skins\Theme\ThemePluginManager;

/**
 * Plugin manager used for tests.
 *
 * @phpstan-ignore-next-line
 */
class DummyThemePluginManager extends ThemePluginManager {

  /**
   * The list of themes.
   *
   * @var array
   */
  protected array $themes = [];

  /**
   * {@inheritdoc}
   */
  public function getDefinitions(): array {
    $definitions = $this->themes;
    foreach ($definitions as $plugin_id => &$definition) {
      $this->processDefinition($definition, $plugin_id);
    }
    $this->definitions = $definitions;
    return $definitions;
  }

  /**
   * Getter.
   *
   * @return array
   *   Property value.
   */
  public function getThemes(): array {
    return $this->themes;
  }

  /**
   * Setter.
   *
   * @param array $themes
   *   Property value.
   *
   * @return $this
   */
  public function setThemes(array $themes) {
    $this->themes = $themes;
    return $this;
  }

}
