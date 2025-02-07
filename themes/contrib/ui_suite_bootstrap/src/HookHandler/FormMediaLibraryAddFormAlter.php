<?php

declare(strict_types=1);

namespace Drupal\ui_suite_bootstrap\HookHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\ui_suite_bootstrap\Utility\Bootstrap;
use Drupal\ui_suite_bootstrap\Utility\Element as UsbElement;

/**
 * Hook implementation.
 */
class FormMediaLibraryAddFormAlter {

  /**
   * Hook implementation.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   * @param string $formId
   *   The form ID.
   */
  public function alter(array &$form, FormStateInterface $formState, string $formId): void {
    $this->styleWrapper($form);
    $this->styleCreatedMediaList($form);
  }

  /**
   * Style the form wrapper, all steps.
   *
   * @param array $form
   *   The form structure.
   */
  protected function styleWrapper(array &$form): void {
    $form['#attributes']['class'][] = 'row';
    $form['#attributes']['class'][] = 'm-1';
    $form['#attributes']['class'][] = 'mb-3';
    $form['#attributes']['class'][] = 'p-2';
    $form['#attributes']['class'][] = 'border';
  }

  /**
   * Style the created media, second step.
   *
   * @param array $form
   *   The form structure.
   */
  protected function styleCreatedMediaList(array &$form): void {
    if (!isset($form['media'])) {
      return;
    }

    $form['media']['#attributes']['class'][] = 'list-unstyled';

    foreach (Element::children($form['media']) as $key) {
      $media = &$form['media'][$key];

      $media['#wrapper_attributes']['class'][] = 'row';

      $media['preview']['#attributes']['class'][] = 'col-2';
      $media['preview']['#attributes']['class'][] = 'bg-light';
      $media['preview']['#attributes']['class'][] = 'd-flex';
      $media['preview']['#attributes']['class'][] = 'align-items-center';
      $media['preview']['#attributes']['class'][] = 'justify-content-center';

      $media['fields']['#attributes']['class'][] = 'col-10';
      $media['fields']['#attributes']['class'][] = 'mt-3';

      // Need CSS for the special 'right', so handle all properties with CSS.
      $media['remove_button']['#attributes']['class'][] = 'media-added-remove-button';

      $element = UsbElement::create($media['remove_button']);
      $element->setIcon(Bootstrap::icon('x-lg'));
    }
  }

}
