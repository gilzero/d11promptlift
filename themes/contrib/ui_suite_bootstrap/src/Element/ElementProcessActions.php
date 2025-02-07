<?php

declare(strict_types=1);

namespace Drupal\ui_suite_bootstrap\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ui_suite_bootstrap\Utility\Element;

/**
 * Element Process methods for actions.
 */
class ElementProcessActions {

  /**
   * Processes an actions form element.
   */
  public static function processActions(array &$element, FormStateInterface $form_state, array &$complete_form): array {
    $element_object = Element::create($element);
    $element_object->addClass('mt-3');

    // Change links into buttons.
    foreach ($element_object->children() as $child) {
      if (!$child->isType('link')) {
        continue;
      }
      $child->addClass('btn');
      $child->colorize();
      $child->setIcon();
    }

    return $element;
  }

}
