<?php

declare(strict_types=1);

namespace Drupal\ui_suite_bootstrap_starterkit\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ui_suite_bootstrap\Element\ElementProcessRadios as UsbsElementProcessRadios;

/**
 * Element Process methods for radios.
 */
class ElementProcessRadios extends UsbsElementProcessRadios {

  /**
   * Processes a radios form element.
   */
  public static function processRadios(array &$element, FormStateInterface $form_state, array &$complete_form): array {
    if (isset($complete_form['#gin_lb_form']) && $complete_form['#gin_lb_form']) {
      return $element;
    }
    return parent::processRadios($element, $form_state, $complete_form);
  }

}
