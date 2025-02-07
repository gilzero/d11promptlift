<?php

declare(strict_types=1);

namespace Drupal\ui_suite_uswds\HookHandler;

/**
 * Preprocess hook for blocks.
 */
class PreprocessBlock {

  /**
   * Preprocess Block Content Hero.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocessContentHero(array &$variables): void {
    $this->markBlockContentItems($variables, 'hero');
  }

  /**
   * Preprocess block__system_menu_block__footer_primary.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocessSystemMenuBlockFooterPrimary(array &$variables): void {
    $this->markMenuItems($variables, 'footer_primary');
  }

  /**
   * Preprocess block__system_menu_block__primary_menu.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocessSystemMenuBlockPrimaryMenu(array &$variables): void {
    $this->markMenuItems($variables, 'primary_menu');
  }

  /**
   * Preprocess block__system_menu_block__secondary_menu.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocessSystemMenuBlockSecondaryMenu(array &$variables): void {
    $this->markMenuItems($variables, 'secondary_menu');
  }

  /**
   * Preprocess block__system_menu_block__sidebar_first.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocessSystemMenuBlockSidebarFirst(array &$variables): void {
    $this->markMenuItems($variables, 'sidebar_first');
  }

  /**
   * Preprocess block__system_menu_block__sidebar_second.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocessSystemMenuBlockSidebarSecond(array &$variables): void {
    $this->markMenuItems($variables, 'sidebar_second');
  }

  /**
   * Helper: mark block-content items as being in one of our block regions.
   *
   * This is the way we communicate a content block's region to its preprocessor
   * and template.
   */
  protected function markBlockContentItems(&$variables, $region): void {
    if (!empty($variables['content'])) {
      $variables['content']['#ui_suite_uswds_region'] = $region;
    }
  }

  /**
   * Helper function to mark menu items as being in one of our menu regions.
   *
   * This is the way we communicate a menu block's region to its preprocessor
   * and template.
   */
  protected function markMenuItems(&$variables, $region): void {
    if (isset($variables['content']['#items'])) {
      foreach ($variables['content']['#items'] as &$item) {
        $item['#ui_suite_uswds_region'] = $region;
      }
    }
  }

}
