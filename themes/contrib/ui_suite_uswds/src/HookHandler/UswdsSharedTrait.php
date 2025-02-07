<?php

namespace Drupal\ui_suite_uswds\HookHandler;

/**
 * Helper functions to use between HookHandler files.
 */
trait UswdsSharedTrait {

  /**
   * Helper function to see if we should alter any menu.
   *
   * @param string $region
   *   The machine name of the region.
   *
   * @return bool
   *   Return if this should process the menu or not.
   */
  protected function processMenuRegion(string $region): bool {
    $menu_bypass = theme_get_setting('uswds_menu_bypass');
    if (empty($menu_bypass)) {
      $menu_bypass = [];
    }
    return (empty($menu_bypass[$region]));
  }

}
