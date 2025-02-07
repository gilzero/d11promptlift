<?php

/**
 * @file
 * Artisan API documentation.
 */

use Drupal\artisan\customizations\ArtisanCustomizations;

/**
 * Implements hook_artisan_customizations_alter().
 *
 * $customizations = [
 *   'GROUP_KEY' => [
 *     'wrapper' => 'base|headings|displays|breadcrumb|buttons|layout|header|footer|responsive|form|component',
 *     'label' => t('Group label.'),
 *     'wrapper_description' => t('Group outer descripcion.'),
 *     'description' => t('Group descripcion.'),
 *     'type_default' => 'color|textfield|number|checkbox',
 *     'selector_default' => ':root|div[data-component-id="COMPONENT"]',
 *     'list' => [
 *       'CUSTOMIZATION_KEY' => [
 *         'label' => t('Label'),
 *         'description' => t('Description'),
 *         'type' => 'color|textfield|number|checkbox',
 *       ],
 *       ...
 *     ]
 *   ],
 *   ...
 * ];
 *
 * @note adjust subtheme schema definition when altered.
 * @note each customization definition will generate:
 *   - CSS Variable "--theme-GROUP_KEY-CUSTOMIZATION_KEY".
 *   - Theme settings "GROUP_KEY_CUSTOMIZATION_KEY".
 */
function hook_artisan_customizations_alter(&$customizations) {
  // Use just primary & secondary with outline & link, discard others.
  // @see ArtisanCustomizations::getDefinitions().
  // @see ArtisanCustomizationsBtnVariantsTrait::getBtnVariantsList().
  // To remove/alter current definition.
  $buttons_to_remove = [
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
  foreach ($buttons_to_remove as $delta) {
    if (!empty($customizations[$delta])) {
      unset($customizations[$delta]);
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
      'size' => [
        'label' => t('Font size'),
        'description' => ArtisanCustomizations::FONT_SIZE_EXAMPLE,
      ],
      'color' => [
        'label' => t('Color'),
        'type' => 'color',
      ],
    ],
  ];
}
