<?php

declare(strict_types=1);

namespace Drupal\ui_suite_bootstrap_starterkit\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ui_suite_bootstrap\Element\ElementProcessInputGroup as UsbsElementProcessInputGroup;

/**
 * Element Process methods for input group feature.
 */
class ElementProcessInputGroup extends UsbsElementProcessInputGroup {

  /**
   * Processes element supporting input group.
   */
  public static function processInputGroup(array &$element, FormStateInterface $form_state, array &$complete_form): array {
    if (isset($complete_form['#gin_lb_form']) && $complete_form['#gin_lb_form']) {
      return $element;
    }
    return parent::processInputGroup($element, $form_state, $complete_form);
  }

}
