<?php

declare(strict_types=1);

namespace Drupal\ui_suite_bootstrap_starterkit\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ui_suite_bootstrap\Element\ElementProcessTextFormat as UsbsElementProcessTextFormat;

/**
 * Element Process methods for text format.
 */
class ElementProcessTextFormat extends UsbsElementProcessTextFormat {

  /**
   * Processes a text format form element.
   */
  public static function processTextFormat(array &$element, FormStateInterface $form_state, array &$complete_form): array {
    if (isset($complete_form['#gin_lb_form']) && $complete_form['#gin_lb_form']) {
      return $element;
    }
    return parent::processTextFormat($element, $form_state, $complete_form);
  }

}
