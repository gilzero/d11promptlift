<?php

declare(strict_types=1);

namespace Drupal\ui_suite_uswds\HookHandler;

/**
 * Preprocess hook for details.
 */
class PreprocessRegion {

  /**
   * Preprocess region.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocess(array &$variables): void {
    if (isset($variables['region'])) {
      $region = $variables['region'];

      switch ($region) {
        case 'header_top':
          $variables['attributes']['class'][] = 'usa-navbar';
          break;

        case 'highlighted':
          $variables['attributes']['class'][] = 'width-full';
          break;

        case 'secondary_menu':
          if (theme_get_setting('uswds_header_style') !== 'basic') {
            $variables['attributes']['class'][] = 'usa-nav__secondary';
          }
          break;

        default:
          break;
      }
    }
  }

}
