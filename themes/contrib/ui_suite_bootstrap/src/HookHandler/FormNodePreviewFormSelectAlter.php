<?php

declare(strict_types=1);

namespace Drupal\ui_suite_bootstrap\HookHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ui_suite_bootstrap\Utility\Bootstrap;
use Drupal\ui_suite_bootstrap\Utility\Element;

/**
 * Hook implementation.
 */
class FormNodePreviewFormSelectAlter {

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
    $element = Element::create($form);

    $element->addClass([
      'row',
      'row-cols-auto',
      'justify-content-center',
      'align-items-center',
      'bg-info-subtle',
      'text-center',
      'pt-3',
      'gap-sm-5',
    ]);

    // Backlink.
    if (isset($element->backlink)) {
      $options = $element->backlink->getProperty('options', []);
      $element->backlink->addClass($options['attributes']['class'] ?? []);
      $element->backlink->addClass(['btn', 'btn-info', 'mb-3']);
      $element->backlink->setIcon((Bootstrap::icon('chevron-left')));

      // Ensure the UUID is set.
      if (isset($element->uuid)) {
        $uuid = $element->uuid->getProperty('value');
        if ($uuid) {
          $options['query'] = ['uuid' => $uuid];
        }
      }
      // Override the options attributes.
      $options['attributes'] = $element->backlink->getAttributes()->getArrayCopy();

      $element->backlink->setProperty('options', $options);
    }

    // View mode.
    if (isset($element->view_mode)) {
      $element->view_mode->setProperty('title_display', 'inline');
      $element->view_mode->addClass(['col-auto', 'pe-0'], Element::LABEL);
      $element->view_mode->addClass('col-auto', Element::INNER_WRAPPER);
    }
  }

}
