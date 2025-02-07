<?php

namespace Drupal\artisan\customizations;

/**
 * Page responsive - Artisan customizations definition.
 */
trait ArtisanCustomizationsResponsiveAccentTrait {

  /**
   * Main definition/s.
   *
   * @return array
   *   Definition.
   */
  protected static function getResponsiveAccentDefinitions() {
    return [
      'responsive_accent' => [
        'wrapper' => 'responsive',
        'label' => t('Responsive accent'),
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
