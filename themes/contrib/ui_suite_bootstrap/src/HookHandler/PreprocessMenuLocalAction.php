<?php

declare(strict_types=1);

namespace Drupal\ui_suite_bootstrap\HookHandler;

use Drupal\ui_suite_bootstrap\Utility\Bootstrap;

/**
 * Add button style to local actions.
 */
class PreprocessMenuLocalAction {

  /**
   * Add button style to local actions.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocess(array &$variables): void {
    $link = $variables['element']['#link'];
    $link += ['localized_options' => []];

    $options = $link['localized_options'] ?? [];

    if (isset($link['url'])) {
      // Turn link into a mini-button and colorize based on title.
      $class = Bootstrap::cssClassFromString($link['title'], 'outline-dark');
      if (!isset($options['attributes']['class'])) {
        $options['attributes']['class'] = [];
      }
      $string = \is_string($options['attributes']['class']);
      if ($string) {
        $options['attributes']['class'] = \explode(' ', $options['attributes']['class']);
      }
      $options['attributes']['class'][] = 'btn';
      $options['attributes']['class'][] = 'btn-sm';
      $options['attributes']['class'][] = 'btn-' . $class;
      if ($string) {
        $options['attributes']['class'] = \implode(' ', $options['attributes']['class']);
      }

      $variables['link'] = [
        '#type' => 'link',
        '#title' => $link['title'],
        '#options' => $options,
        '#url' => $link['url'],
        '#icon' => Bootstrap::iconFromString($link['title']),
      ];
    }
    else {
      $variables['link'] = [
        '#type' => 'link',
        '#title' => $link['title'],
        '#options' => $options,
        '#url' => $link['url'],
      ];
    }
  }

}
