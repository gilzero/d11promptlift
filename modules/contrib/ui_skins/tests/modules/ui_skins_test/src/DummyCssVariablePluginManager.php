<?php

declare(strict_types=1);

namespace Drupal\ui_skins_test;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\ui_skins\CssVariable\CssVariablePluginManager;

/**
 * Plugin manager used for tests.
 *
 * @phpstan-ignore-next-line
 */
class DummyCssVariablePluginManager extends CssVariablePluginManager {

  /**
   * The list of CSS variables.
   *
   * @var array
   */
  protected array $cssVariables = [];

  /**
   * {@inheritdoc}
   *
   * @phpstan-ignore-next-line
   */
  public function __construct(
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler,
    ThemeHandlerInterface $theme_handler,
    TranslationInterface $translation,
  ) {
    $this->stringTranslation = $translation;
    parent::__construct($cache_backend, $module_handler, $theme_handler);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions(): array {
    $definitions = $this->cssVariables;
    foreach ($definitions as $plugin_id => &$definition) {
      $this->processDefinition($definition, $plugin_id);
    }
    return $definitions;
  }

  /**
   * Getter.
   *
   * @return array
   *   Property value.
   */
  public function getCssVariables(): array {
    return $this->cssVariables;
  }

  /**
   * Setter.
   *
   * @param array $cssVariables
   *   Property value.
   *
   * @return $this
   */
  public function setCssVariables(array $cssVariables) {
    $this->cssVariables = $cssVariables;
    return $this;
  }

}
