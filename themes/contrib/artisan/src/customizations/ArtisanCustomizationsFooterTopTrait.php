<?php

namespace Drupal\artisan\customizations;

/**
 * Page footer top - Artisan customizations definition.
 */
trait ArtisanCustomizationsFooterTopTrait {

  /**
   * Main definition/s.
   *
   * @return array
   *   Definition.
   */
  protected static function getFooterTopDefinitions() {
    return [
      'footer_top' => [
        'wrapper' => 'footer',
        'label' => t('Footer Top'),
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
