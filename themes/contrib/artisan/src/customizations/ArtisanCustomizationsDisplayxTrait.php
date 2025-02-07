<?php

namespace Drupal\artisan\customizations;

/**
 * Heading displays - Artisan customizations definition.
 */
trait ArtisanCustomizationsDisplayxTrait {

  /**
   * Main definition/s.
   *
   * @return array
   *   Definition.
   */
  protected static function getDisplayxDefinitions() {
    $definitions = [];
    $definitions['displays'] = [
      'wrapper' => 'displays',
      'label' => t('Global'),
      'type_default' => 'textfield',
      'selector_default' => ':root',
      'list' => [
        'font_family' => ArtisanCustomizations::getDefaultDefinition('font_family'),
        'font_weight' => ArtisanCustomizations::getDefaultDefinition('font_weight'),
        'decoration' => ArtisanCustomizations::getDefaultDefinition('decoration'),
        'color' => ArtisanCustomizations::getDefaultDefinition('color'),
      ],
    ];
    foreach ([1, 2, 3, 4, 5, 6] as $heading_number) {
      $definitions['display' . $heading_number] = [
        'wrapper' => 'displays',
        'label' => t('Display :number', [
          ':number' => $heading_number,
        ]),
        'type_default' => 'textfield',
        'selector_default' => ':root',
        'list' => [
          'font_size' => ArtisanCustomizations::getDefaultDefinition('font_size'),
          'font_size_lg' => ArtisanCustomizations::getDefaultDefinition('font_size_lg'),
          'font_family' => ArtisanCustomizations::getDefaultDefinition('font_family'),
          'font_weight' => ArtisanCustomizations::getDefaultDefinition('font_weight'),
          'decoration' => ArtisanCustomizations::getDefaultDefinition('decoration'),
          'color' => ArtisanCustomizations::getDefaultDefinition('color'),
        ],
      ];
    }
    return $definitions;
  }

}
