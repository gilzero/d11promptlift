<?php

declare(strict_types=1);

namespace Drupal\ui_suite_bootstrap\HookHandler;

/**
 * Handle CSS classes.
 */
class PreprocessPage {

  /**
   * Handle CSS classes.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocess(array &$variables): void {
    $variables['container'] = \theme_get_setting('container') ?? 'container';
  }

}
