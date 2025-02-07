<?php

declare(strict_types=1);

namespace Drupal\ui_suite_uswds\HookHandler;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Preprocess hook for menus.
 */
class PreprocessMenu implements ContainerInjectionInterface {

  use UswdsSharedTrait;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   */
  public function __construct(ModuleHandlerInterface $moduleHandler) {
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    // @phpstan-ignore-next-line
    return new static(
      $container->get('module_handler')
    );
  }

  /**
   * Preprocess Menus.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocessMenu(array &$variables): void {
    $variables['use_megamenu'] = theme_get_setting('uswds_header_mega');
  }

  /**
   * Preprocess Primary Menu.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocessPrimaryMenu(array &$variables): void {
    $variables['megamenu'] = theme_get_setting('uswds_header_mega');
  }

  /**
   * Preprocess Secondary Menu.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocessSecondaryMenu(array &$variables): void {
    if ($this->moduleHandler->moduleExists('search') && theme_get_setting('uswds_search')) {
      // If this is a basic header, we put the search form after the menu.
      if ('extended' == theme_get_setting('uswds_header_style')) {
        $search_item = t('<li class="js-search-button-container"><button type="submit" class="usa-header-search-button js-search-button">@search</button></li>', ['@search' => 'Search']);
        $variables['search_item'] = $search_item;
      }
    }
  }

  /**
   * Preprocess Menu Local Tasks.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocessMenuLocalTasks(array &$variables): void {
    if (!empty($variables['primary'])) {
      foreach ($variables['primary'] as $menu_item_key => $menu_attributes) {
        $variables['primary'][$menu_item_key]['#link']['localized_options']['attributes']['class'][] = 'usa-button';
        if (!empty($variables['primary'][$menu_item_key]['#active'])) {
          $variables['primary'][$menu_item_key]['#link']['localized_options']['attributes']['class'][] = 'usa-button--active';
        }
      }
    }
    if (!empty($variables['secondary'])) {
      foreach ($variables['secondary'] as $menu_item_key => $menu_attributes) {
        $variables['secondary'][$menu_item_key]['#link']['localized_options']['attributes']['class'][] = 'usa-button';
        $variables['secondary'][$menu_item_key]['#link']['localized_options']['attributes']['class'][] = 'usa-button-secondary';
        if (!empty($variables['secondary'][$menu_item_key]['#active'])) {
          $variables['secondary'][$menu_item_key]['#link']['localized_options']['attributes']['class'][] = 'usa-button--active';
        }
      }
    }
  }

  /**
   * Preprocess Menu Local Action.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocessMenuLocalAction(array &$variables): void {
    $variables['link']['#options']['attributes']['class'][] = 'usa-button';
    $variables['link']['#options']['attributes']['class'][] = 'usa-button-outline';
  }

}
