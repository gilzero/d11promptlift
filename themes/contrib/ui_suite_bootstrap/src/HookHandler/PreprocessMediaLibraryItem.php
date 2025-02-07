<?php

declare(strict_types=1);

namespace Drupal\ui_suite_bootstrap\HookHandler;

use Drupal\ui_suite_bootstrap\Utility\Bootstrap;
use Drupal\ui_suite_bootstrap\Utility\Element;

/**
 * Add icons in media library.
 */
class PreprocessMediaLibraryItem {

  /**
   * Add icons in media library view.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocess(array &$variables): void {
    if (!isset($variables['content']['remove_button'])) {
      return;
    }

    $element = Element::create($variables['content']['remove_button']);
    $element->setIcon(Bootstrap::icon('trash', 'bootstrap', [
      'size' => '16px',
      'alt' => $element->getProperty('value'),
    ]));
    $element->setProperty('icon_position', 'icon_only');
    $element->addClass('btn-sm');
  }

}
