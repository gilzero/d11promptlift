<?php

declare(strict_types=1);

namespace Drupal\ui_suite_bootstrap\HookHandler;

use Drupal\Core\Form\FormStateInterface;

/**
 * Views exposed form.
 */
class FormViewsExposedFormAlter {

  /**
   * Views exposed form.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   * @param string $form_id
   *   The form ID.
   */
  public function alter(array &$form, FormStateInterface $formState, string $form_id): void {
    $form['#attributes']['class'][] = 'row';
    $form['#attributes']['class'][] = 'row-cols-auto';
    $form['#attributes']['class'][] = 'align-items-end';

    if (isset($form['actions'])) {
      $form['actions']['#attributes']['class'][] = 'mb-3';
    }
    // Reset button.
    if (isset($form['actions']['reset'])) {
      $form['actions']['reset']['#attributes']['class'][] = 'ms-2';
    }

    if (!\str_starts_with($form['#id'], 'views-exposed-form-media-library-widget')) {
      return;
    }
    $form['#attributes']['class'][] = 'm-1';
    $form['#attributes']['class'][] = 'mb-3';
    $form['#attributes']['class'][] = 'p-2';
    $form['#attributes']['class'][] = 'border';
  }

}
