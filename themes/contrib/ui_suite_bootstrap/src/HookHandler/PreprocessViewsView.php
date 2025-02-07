<?php

declare(strict_types=1);

namespace Drupal\ui_suite_bootstrap\HookHandler;

use Drupal\ui_suite_bootstrap\Utility\Bootstrap;
use Drupal\ui_suite_bootstrap\Utility\Element;

/**
 * Add icons in media library.
 */
class PreprocessViewsView {

  /**
   * Add icons in media library view.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocess(array &$variables): void {
    /** @var \Drupal\views\ViewExecutable $view */
    $view = $variables['view'];
    if ($view->id() != 'media_library') {
      return;
    }

    if (empty($variables['header'])) {
      return;
    }

    $icons = [
      'widget' => 'grid-fill',
      'widget_table' => 'list-ul',
    ];

    foreach ($variables['header'] as $headerId => $header) {
      if (isset($header['#type'])
        && $header['#type'] == 'link'
        && isset($header['#options']['view'], $header['#options']['target_display_id'], $icons[$header['#options']['target_display_id']])
      ) {
        $element = Element::create($variables['header'][$headerId]);
        $element->setIcon(Bootstrap::icon($icons[$header['#options']['target_display_id']]));
      }
    }
  }

}
