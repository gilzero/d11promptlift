<?php

/**
 * @file
 * Funtions to alter customizations.
 */

use Drupal\artisan\customizations\ArtisanCustomizations;

/**
 * Implements hook_artisan_customizations_alter().
 */
function artisan_starterkit_artisan_customizations_alter(&$customizations) {
  // Use just primary & secondary with outline & link, discard others.
  // @see ArtisanCustomizations::getDefinitions().
  // @see ArtisanCustomizationsBtnVariantsTrait::getBtnVariantsList().
  // Add here customizations group delta, customization delta or combined to
  // exclude from theme settings. E.g: 'btn', 'color', 'btn_color'.
  $skip_like = [
    'btn_success',
    'btn_outline_success',
    'btn_danger',
    'btn_outline_danger',
    'btn_warning',
    'btn_outline_warning',
    'btn_info',
    'btn_outline_info',
    'btn_light',
    'btn_outline_light',
    'btn_dark',
    'btn_outline_dark',
  ];
  $skip_pattern = '/(?:' . implode('|', $skip_like) . ')/i';
  foreach ($customizations as $group_delta => $customization) {
    foreach (array_keys($customization['list'] ?? []) as $customization_delta) {
      if (preg_match($skip_pattern, $group_delta . '_' . $customization_delta)) {
        unset($customizations[$group_delta]['list'][$customization_delta]);
      }
    }
    if (empty($customizations[$group_delta]['list'])) {
      unset($customizations[$group_delta]);
    }
  }
  // Add example component definition.
  $customizations['example'] = [
    'wrapper' => 'component',
    'label' => t('Example'),
    'description' => t('See hook_artisan_customizations_alter() & artisan_starterkit_artisan_customizations_alter(). These customizations will be available as css variables under "div[data-component-id="artisan_starterkit:example"]" selector. E.g: "div[data-component-id="artisan_starterkit:example"] { color: var(--theme-example-color); }.'),
    'type_default' => 'textfield',
    'selector_default' => 'div[data-component-id="artisan_starterkit:example"]',
    'list' => [
      'font_size' => ArtisanCustomizations::getDefaultDefinition('font_size'),
      'color' => ArtisanCustomizations::getDefaultDefinition('color'),
    ],
  ];
}
