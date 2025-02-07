<?php

declare(strict_types=1);

namespace Drupal\ui_suite_uswds\HookHandler;

/**
 * Preprocess hook for details.
 */
class PreprocessForms {

  /**
   * Preprocess Form Elements.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocessFormElement(array &$variables): void {
    // Add an element type for the label.
    $type = $variables['element']['#type'];
    $variables['label']['#element_type'] = $type;

    if (!empty($variables['errors'])) {
      $variables['attributes']['class'][] = 'usa-input-error';

      if (!empty($variables['element']['#id'])) {
        $variables['error_id'] = $variables['element']['#id'];
      }
      elseif (!empty($variables['element']['#attributes']['id'])) {
        $variables['error_id'] = $variables['element']['#attributes']['id'];
      }
    }
  }

  /**
   * Preprocess Form Element Labels.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocessFormElementLabels(array &$variables): void {
    if (!empty($variables['element']['#element_type']) && $variables['element']['#element_type'] == 'checkbox') {
      $variables['is_checkbox'] = TRUE;
    }
    else {
      $variables['is_checkbox'] = FALSE;
    }

    if (!empty($variables['element']['#element_type']) && $variables['element']['#element_type'] == 'radio') {
      $variables['is_radio'] = TRUE;
    }
    else {
      $variables['is_radio'] = FALSE;
    }
  }

}
