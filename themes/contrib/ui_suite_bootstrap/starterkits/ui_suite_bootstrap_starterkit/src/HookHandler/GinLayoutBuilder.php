<?php

declare(strict_types=1);

namespace Drupal\ui_suite_bootstrap_starterkit\HookHandler;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implementation of hooks needed to support Gin Layout Builder.
 */
class GinLayoutBuilder implements ContainerInjectionInterface {

  /**
   * Needle to find theme registry element proper to Gin LB.
   */
  public const GIN_LB_NEEDLE = '__gin_lb';

  /**
   * The contrib base theme.
   */
  public const BASE_THEME = 'ui_suite_bootstrap';

  /**
   * The theme providing Gin LB support.
   *
   * It contains the process callback overrides.
   */
  public const GIN_LB_SUPPORT_THEME = 'ui_suite_bootstrap_starterkit';

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected ThemeHandlerInterface $themeHandler;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $themeHandler
   *   The theme handler.
   */
  public function __construct(
    ModuleHandlerInterface $moduleHandler,
    ThemeHandlerInterface $themeHandler,
  ) {
    $this->moduleHandler = $moduleHandler;
    $this->themeHandler = $themeHandler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('module_handler'),
      $container->get('theme_handler')
    );
  }

  /**
   * Remove process functions from base theme to add ours.
   *
   * Ours checks for Gin LB.
   *
   * @param array $info
   *   An associative array with structure identical to that of the return value
   *   of \Drupal\Core\Render\ElementInfoManagerInterface::getInfo().
   */
  public function elementInfoAlter(array &$info): void {
    if (!$this->moduleHandler->moduleExists('gin_lb')) {
      return;
    }

    foreach ($info as &$element_info) {
      if (!isset($element_info['#process']) || !\is_array($element_info['#process'])) {
        continue;
      }

      foreach ($element_info['#process'] as &$process_callback) {
        if (!\is_array($process_callback)) {
          continue;
        }
        if (!\is_string($process_callback[0])) {
          continue;
        }

        $process_callback[0] = \str_replace(static::BASE_THEME, static::GIN_LB_SUPPORT_THEME, $process_callback[0]);
      }
    }
  }

  /**
   * Remove preprocess functions from front themes when on Gin LB element.
   *
   * @param array $theme_registry
   *   The entire cache of theme registry information, post-processing.
   */
  public function themeRegistryAlter(array &$theme_registry): void {
    if (!$this->moduleHandler->moduleExists('gin_lb')) {
      return;
    }

    $themes = $this->themeHandler->listInfo();
    $default_theme = $this->themeHandler->getDefault();
    // Create a list which includes the default theme and all its base themes.
    // We suppose that the default theme is different than the admin theme.
    if (isset($themes[$default_theme]->base_themes)) {
      $theme_keys = \array_keys($themes[$default_theme]->base_themes);
      $theme_keys[] = $default_theme;
    }
    else {
      $theme_keys = [$default_theme];
    }

    foreach ($theme_registry as $theme_key => &$theme_definition) {
      if (!\str_contains($theme_key, static::GIN_LB_NEEDLE)) {
        continue;
      }

      if (!isset($theme_definition['preprocess functions'])) {
        continue;
      }

      foreach ($theme_definition['preprocess functions'] as $key => $preprocess_hook) {
        foreach ($theme_keys as $front_theme) {
          if (\str_contains($preprocess_hook, (string) $front_theme)) {
            unset($theme_definition['preprocess functions'][$key]);
          }
        }
      }
    }
  }

  /**
   * Ensure Gin LB suggestions are over UI Suite Bootstrap suggestions.
   *
   * @param array $suggestions
   *   An array of theme suggestions.
   * @param array $variables
   *   An array of variables passed to the theme hook. Note that this hook is
   *   invoked before any preprocessing.
   */
  public function themeSuggestionsInputAlter(array &$suggestions, array $variables): void {
    if (!$this->moduleHandler->moduleExists('gin_lb')) {
      return;
    }
    \gin_lb_theme_suggestions_alter($suggestions, $variables, 'input');
  }

}
