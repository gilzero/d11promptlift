<?php

namespace Drupal\artisan\customizations;

/**
 * Base font - Artisan customizations definition.
 */
trait ArtisanCustomizationsBaseFontTrait {

  /**
   * Main definition/s.
   *
   * @return array
   *   Definition.
   */
  protected static function getBaseFontDefinitions() {
    return [
      'base_font' => [
        'wrapper' => 'base',
        'label' => t('Base font'),
        'type_default' => 'textfield',
        'selector_default' => ':root',
        'list' => [
          'size' => ArtisanCustomizations::getDefaultDefinition('font_size'),
          'weight' => ArtisanCustomizations::getDefaultDefinition('font_weight'),
          'family' => ArtisanCustomizations::getDefaultDefinition('font_family'),
          'line_height' => ArtisanCustomizations::getDefaultDefinition('line_height'),
        ],
      ],
    ];
  }

}
