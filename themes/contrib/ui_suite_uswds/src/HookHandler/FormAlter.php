<?php

declare(strict_types=1);

namespace Drupal\ui_suite_uswds\HookHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Alter forms.
 */
class FormAlter {

  /**
   * Alter forms.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   * @param string $form_id
   *   The form ID.
   */
  public function alter(array &$form, FormStateInterface $formState, string $form_id): void {
    $this->attachFormId($form, $form_id);
  }

  /**
   * Attaches form id to all form elements.
   *
   * @param array $form
   *   The form or form element which children should have form id attached.
   * @param string $form_id
   *   The form id attached to form elements.
   */
  protected function attachFormId(array &$form, string $form_id): void {
    foreach (Element::children($form) as $child) {
      if (!isset($form[$child]['#form_id'])) {
        $form[$child]['#form_id'] = $form_id;
      }
      $this->attachFormId($form[$child], $form_id);
    }
  }

}
