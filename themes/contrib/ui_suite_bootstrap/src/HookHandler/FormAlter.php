<?php

declare(strict_types=1);

namespace Drupal\ui_suite_bootstrap\HookHandler;

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
    // See \Drupal\Core\Form\FormBuilder::processForm().
    if ($formState->isMethodType('get') && $formState->getAlwaysProcess()) {
      $form['#after_build'][] = [
        static::class,
        'afterBuildAddGetFormMethod',
      ];
    }

    if ($this->isOffcanvasForm($form, $form_id)) {
      // Even with the after build, some elements like actions is not marked,
      // so marked it directly.
      static::markOffcanvas($form);
      // Use #after_build otherwise we do not have access to all subform
      // elements.
      $form['#after_build'][] = [
        static::class,
        'afterBuildMarkLayoutBuilder',
      ];
    }

    // Default styling for Layout Builder "main" form.
    // There is no specific form ID to target as the base form ID is dynamic in
    // LayoutBuilderEntityFormTrait.
    if (\str_ends_with($form_id, '_layout_builder_form')) {
      $this->alterLayoutBuilderForm($form, $formState);
    }

    // Default styling for views bulk actions forms. There is no specific form
    // ID to target.
    if (\str_starts_with($form['#id'], 'views-form')) {
      $this->alterViewsBulkActionForm($form);
    }
  }

  /**
   * Form element #after_build callback.
   */
  public static function afterBuildAddGetFormMethod(array $element, FormStateInterface $form_state): array {
    static::addGetFormMethod($element);
    return $element;
  }

  /**
   * Form element #after_build callback.
   */
  public static function afterBuildMarkLayoutBuilder(array $element, FormStateInterface $form_state): array {
    static::markOffcanvas($element);
    return $element;
  }

  /**
   * Set form method to all form elements.
   *
   * To allow other processing to act based on this information.
   *
   * @param array $form
   *   The form or form element which children should have form method attached.
   */
  protected static function addGetFormMethod(array &$form): void {
    foreach (Element::children($form) as $child) {
      if (!isset($form[$child]['#form_method'])) {
        $form[$child]['#form_method'] = 'get';
      }
      static::addGetFormMethod($form[$child]);
    }
  }

  /**
   * Detect if Layout Builder offcanvas form.
   *
   * @param array $form
   *   The form structure.
   * @param string $form_id
   *   The form ID.
   *
   * @return bool
   *   True for offcanvas form.
   *
   * @see gin_lb_is_layout_builder_form_id()
   */
  protected function isOffcanvasForm(array &$form, string $form_id): bool {
    $form_ids = [
      'layout_builder_add_block',
      'layout_builder_block_move',
      'layout_builder_configure_section',
      'layout_builder_remove_block',
      'layout_builder_remove_section',
      'layout_builder_update_block',
      'layout_builder_block_clone.clone_block_form',
      'section_library_add_section_to_library',
      'section_library_add_template_to_library',
    ];
    if (\in_array($form_id, $form_ids, TRUE)) {
      return TRUE;
    }

    $form_id_contains = [
      'layout_builder_translate_form',
    ];
    foreach ($form_id_contains as $form_id_contain) {
      if (\str_contains($form_id, $form_id_contain)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Set isOffcanvas to all form elements.
   *
   * @param array $form
   *   The form or form element which children should have form id attached.
   */
  protected static function markOffcanvas(array &$form): void {
    foreach (Element::children($form) as $child) {
      if (!isset($form[$child]['#isOffcanvas'])) {
        $form[$child]['#isOffcanvas'] = TRUE;
      }
      static::markOffcanvas($form[$child]);
    }
  }

  /**
   * Default styling for Layout Builder "main" form.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   */
  protected function alterLayoutBuilderForm(array &$form, FormStateInterface $formState): void {
    $form['actions']['#attributes']['class'][] = 'mb-3';

    // Move preview checkbox out of actions so buttons are aligned.
    $form['preview_toggle'] = $form['actions']['preview_toggle'];
    unset($form['actions']['preview_toggle']);
  }

  /**
   * Default styling for views bulk actions forms.
   *
   * @param array $form
   *   The form structure.
   */
  protected function alterViewsBulkActionForm(array &$form): void {
    if (!isset($form['header'])) {
      return;
    }

    $headerElements = Element::children($form['header']);
    foreach ($headerElements as $headerElement) {
      if (!\str_ends_with($headerElement, '_bulk_form')) {
        continue;
      }

      $form['header'][$headerElement]['#attributes']['class'][] = 'row';
      $form['header'][$headerElement]['#attributes']['class'][] = 'row-cols-auto';
      $form['header'][$headerElement]['#attributes']['class'][] = 'align-items-end';
      if (isset($form['header'][$headerElement]['actions'])) {
        $form['header'][$headerElement]['actions']['#attributes']['class'][] = 'mb-3';
      }
    }
  }

}
