<?php

declare(strict_types=1);

namespace Drupal\ui_suite_bootstrap\HookHandler;

/**
 * Add theme key variables.
 */
class ThemeRegistryAlter {

  /**
   * Alter the theme registry.
   *
   * @param array $themeRegistry
   *   The entire cache of theme registry information, post-processing.
   */
  public function alter(array &$themeRegistry): void {
    foreach ($themeRegistry as $themeKey => $themeDefinition) {
      // Skip theme hooks that don't set variables.
      if (!isset($themeDefinition['variables'])) {
        continue;
      }
      $themeRegistry[$themeKey]['variables'] += [
        'context' => [],
      ];
    }
  }

}
