<?php

declare(strict_types=1);

namespace Drupal\ui_suite_bootstrap\HookHandler;

use Drupal\ui_suite_bootstrap\Utility\Variables;

/**
 * Add theme suggestions.
 */
class ThemeSuggestionsAlter {

  /**
   * The Variables object.
   *
   * @var \Drupal\ui_suite_bootstrap\Utility\Variables
   */
  protected $variables;

  /**
   * An element object provided in the variables array, may not be set.
   *
   * @var \Drupal\ui_suite_bootstrap\Utility\Element|false
   */
  protected $element;

  /**
   * Template suggestions for details.
   *
   * @param array $suggestions
   *   The list of suggestions.
   * @param array $variables
   *   The theme variables.
   */
  public function details(array &$suggestions, array $variables): void {
    $this->variables = Variables::create($variables);
    $this->element = $this->variables->element;

    if ($this->element && $this->element->getProperty('bootstrap_accordion', TRUE)) {
      $suggestions[] = 'details__accordion';
    }
  }

  /**
   * Template suggestions for input.
   *
   * @param array $suggestions
   *   The list of suggestions.
   * @param array $variables
   *   The theme variables.
   */
  public function input(array &$suggestions, array $variables): void {
    $this->variables = Variables::create($variables);
    $this->element = $this->variables->element;

    if ($this->element && $this->element->isButton()) {
      $hook = 'input__button';
      $suggestions[] = $hook;
    }
  }

  /**
   * Template suggestions for links.
   *
   * @param array $suggestions
   *   The list of suggestions.
   * @param array $variables
   *   The theme variables.
   */
  public function links(array &$suggestions, array $variables): void {
    if (isset($variables['context']['usb_suggestion'])) {
      $suggestions[] = $variables['context']['usb_suggestion'];
    }
  }

}
