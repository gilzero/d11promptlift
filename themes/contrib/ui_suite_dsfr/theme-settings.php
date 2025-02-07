<?php

/**
 * @file
 * Functions to support theme settings in the UI Suite DSFR theme.
 */

/**
 * Implements hook_form_system_theme_settings_alter().
 */
function ui_suite_dsfr_form_system_theme_settings_alter(&$form, $form_state, $form_id = NULL) {
  // Work-around for a core bug affecting admin themes. See issue #943212.
  if (isset($form_id)) {
    return;
  }

  $form['ui_suite_dsfr_utilities'] = [
    '#type' => 'details',
    '#title' => t('UI Suite DSFR Utilities'),
    '#open' => TRUE,
  ];

  $form['ui_suite_dsfr_utilities']['container'] = [
    '#type' => 'select',
    '#title' => t('Container'),
    '#default_value' => theme_get_setting('container'),
    '#description' => t('Select the type of container that will be used on your site.'),
    '#options' => [
      'fr-container' => t('Default'),
      'fr-container--fluid' => t('Fluid'),
    ],
  ];
  $theme = _ui_suite_dsfr_get_theme_name($form);
  $colors = _ui_suite_dsfr_get_colors_options($theme);
  if (isset($colors)) {
    $form['ui_suite_dsfr_utilities']['available_colors'] = [
      '#type' => 'select',
      '#title' => t('Available colors'),
      '#multiple' => TRUE,
      '#size' => 12,
      '#default_value' => theme_get_setting('available_colors'),
      '#description' => t('If no selected - then all will be available. For more info visit <a href="https://www.systeme-de-design.gouv.fr/elements-d-interface/fondamentaux-de-l-identite-de-l-etat/couleurs-utilisation-dans-le-dsfr/" target="_blank">DSFR Colors</a> page.'),
      '#options' => $colors,
    ];
  }

  $form['ui_suite_dsfr_utilities']['logo_text'] = [
    '#type' => 'textfield',
    '#title' => t('Logo text for header and footer'),
    '#default_value' => theme_get_setting('logo_text'),
  ];

  $form['ui_suite_dsfr_utilities']['dsfr_footer_top'] = _menu_settings_form(
    t("Footer top menu"),
    theme_get_setting('dsfr_footer_top') ?? "account"
  );
}
