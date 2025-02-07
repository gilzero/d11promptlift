<?php

namespace Drupal\artisan\customizations;

/**
 * Page header - Artisan customizations definition.
 */
trait ArtisanCustomizationsHeaderLinksTrait {

  /**
   * Main definition/s.
   *
   * @return array
   *   Definition.
   */
  protected static function getHeaderLinksDefinitions() {
    return [
      'header_links' => [
        'wrapper' => 'header',
        'label' => t('Header Links'),
        'type_default' => 'textfield',
        'selector_default' => ':root',
        'list' => [
          'font_family' => ArtisanCustomizations::getDefaultDefinition('font_family'),
          'font_size' => ArtisanCustomizations::getDefaultDefinition('font_size'),
          'font_weight' => ArtisanCustomizations::getDefaultDefinition('font_weight'),
          'decoration' => ArtisanCustomizations::getDefaultDefinition('decoration'),
          'color' => ArtisanCustomizations::getDefaultDefinition('color'),
          'accent_color' => ArtisanCustomizations::getDefaultDefinition('accent_color'),
        ],
      ],
    ];
  }

}
