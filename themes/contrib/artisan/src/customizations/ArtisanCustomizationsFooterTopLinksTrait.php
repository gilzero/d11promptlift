<?php

namespace Drupal\artisan\customizations;

/**
 * Page footer top links - Artisan customizations definition.
 */
trait ArtisanCustomizationsFooterTopLinksTrait {

  /**
   * Main definition/s.
   *
   * @return array
   *   Definition.
   */
  protected static function getFooterTopLinksDefinitions() {
    return [
      'footer_top_links' => [
        'wrapper' => 'footer',
        'label' => t('Footer Top Links'),
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
