<?php

namespace Drupal\artisan\customizations;

/**
 * Heading variants - Artisan customizations definition.
 */
trait ArtisanCustomizationsHxTrait {

  /**
   * Main definition/s.
   *
   * @return array
   *   Definition.
   */
  protected static function getHxDefinitions() {
    $definitions = [];
    foreach ([1, 2, 3, 4, 5, 6] as $heading_number) {
      $definitions['h' . $heading_number] = [
        'wrapper' => 'headings',
        'label' => t('Heading :number', [
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
