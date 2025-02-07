<?php

/**
 * @file
 * Theme and preprocess functions for forms.
 */

/**
 * Implements hook_preprocess_input().
 */
function artisan_preprocess_input(&$variables) {
  // Make sure all inputs has placeholer attribute defined (bs floating labels).
  $type = $variables['element']['#type'] ?? NULL;
  if (!empty(($variables['element']['#title'] ?? NULL)) && empty(($variables['element']['#placeholder'] ?? NULL)) && !empty($type) && !in_array($type, [
    'checkbox',
    'radio',
    'submit',
  ])) {
    $variables['attributes']['placeholder'] = '';
  }
}

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function artisan_theme_suggestions_form_element_alter(array &$suggestions, array &$variables) {
  $element = $variables['element'];
  if (isset($element['#type'])) {
    $suggestions[] = $variables['theme_hook_original'] . '__' . $element['#type'];
  }
}

/**
 * Implements hook_preprocess_page__user__login().
 */
function artisan_preprocess_form_element(&$variables) {
  $variables['floating_label'] = theme_get_setting('form_element_floating_label') ?? FALSE;
  $variables['switches'] = theme_get_setting('form_element_switches') ?? FALSE;
  $variables['inline'] = theme_get_setting('form_element_inline') ?? FALSE;
}
