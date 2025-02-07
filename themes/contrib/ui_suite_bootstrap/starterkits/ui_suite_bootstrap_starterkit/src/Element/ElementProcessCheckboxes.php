<?php

declare(strict_types=1);

namespace Drupal\ui_suite_bootstrap_starterkit\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ui_suite_bootstrap\Element\ElementProcessCheckboxes as UsbsElementProcessCheckboxes;

/**
 * Element Process methods for checkboxes.
 */
class ElementProcessCheckboxes extends UsbsElementProcessCheckboxes {

  /**
   * Processes a checkboxes form element.
   */
  public static function processCheckboxes(array &$element, FormStateInterface $form_state, array &$complete_form): array {
    if (isset($complete_form['#gin_lb_form']) && $complete_form['#gin_lb_form']) {
      return $element;
    }
    return parent::processCheckboxes($element, $form_state, $complete_form);
  }

}
