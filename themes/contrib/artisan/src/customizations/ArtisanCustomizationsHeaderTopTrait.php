<?php

namespace Drupal\artisan\customizations;

/**
 * Page header top - Artisan customizations definition.
 */
trait ArtisanCustomizationsHeaderTopTrait {

  /**
   * Main definition/s.
   *
   * @return array
   *   Definition.
   */
  protected static function getHeaderTopDefinitions() {
    return [
      'header_top' => [
        'wrapper' => 'header',
        'label' => t('Header Top'),
        'type_default' => 'color',
        'selector_default' => ':root',
        'list' => [
          'color' => ArtisanCustomizations::getDefaultDefinition('color'),
          'background' => ArtisanCustomizations::getDefaultDefinition('background_color'),
        ],
      ],
    ];
  }

}
