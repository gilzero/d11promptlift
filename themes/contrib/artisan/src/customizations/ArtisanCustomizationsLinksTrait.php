<?php

namespace Drupal\artisan\customizations;

/**
 * Links - Artisan customizations definition.
 */
trait ArtisanCustomizationsLinksTrait {

  /**
   * Main definition/s.
   *
   * @return array
   *   Definition.
   */
  protected static function getLinksDefinitions() {
    return [
      'links' => [
        'wrapper' => 'base',
        'label' => t('Links'),
        'type_default' => 'textfield',
        'selector_default' => ':root',
        'list' => [
          'font_family' => ArtisanCustomizations::getDefaultDefinition('font_family'),
          'font_weight' => ArtisanCustomizations::getDefaultDefinition('font_weight'),
          'decoration' => ArtisanCustomizations::getDefaultDefinition('decoration'),
          'color' => ArtisanCustomizations::getDefaultDefinition('color'),
          'accent_color' => ArtisanCustomizations::getDefaultDefinition('accent_color'),
        ],
      ],
    ];
  }

}
