<?php

/**
 * @file
 * Functions to support UI Suite Bootstrap theme settings.
 */

declare(strict_types=1);

use Drupal\Core\Form\FormState;

/**
 * Implements hook_form_system_theme_settings_alter().
 */
function ui_suite_bootstrap_form_system_theme_settings_alter(array &$form, FormState $form_state): void {
  $form['ui_suite_bootstrap'] = [
    '#type' => 'details',
    '#title' => \t('Bootstrap'),
    '#open' => TRUE,
  ];

  $form['ui_suite_bootstrap']['container'] = [
    '#type' => 'select',
    '#title' => \t('Page container'),
    '#description' => \t('Select an option for <a href=":url">Bootstrap containers</a>.', [
      ':url' => 'https://getbootstrap.com/docs/5.3/layout/containers/',
    ]),
    '#options' => [
      'container' => \t('Container'),
      'container-sm' => \t('Container small'),
      'container-md' => \t('Container medium'),
      'container-lg' => \t('Container large'),
      'container-xl' => \t('Container x-large'),
      'container-xxl' => \t('Container xx-large'),
      'container-fluid' => \t('Container fluid'),
    ],
    '#default_value' => \theme_get_setting('container') ?? 'container',
  ];
}
