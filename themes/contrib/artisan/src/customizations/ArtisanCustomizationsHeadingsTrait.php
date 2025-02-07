<?php

namespace Drupal\artisan\customizations;

/**
 * Headings - Artisan customizations definition.
 */
trait ArtisanCustomizationsHeadingsTrait {

  /**
   * Main definition/s.
   *
   * @return array
   *   Definition.
   */
  protected static function getHeadingsDefinitions() {
    return [
      'headings' => [
        'wrapper' => 'headings',
        'label' => t('Global'),
        'type_default' => 'textfield',
        'selector_default' => ':root',
        'list' => [
          'font_family' => ArtisanCustomizations::getDefaultDefinition('font_family'),
          'font_weight' => ArtisanCustomizations::getDefaultDefinition('font_weight'),
          'decoration' => ArtisanCustomizations::getDefaultDefinition('decoration'),
          'color' => ArtisanCustomizations::getDefaultDefinition('color'),
        ],
      ],
    ];
  }

}
