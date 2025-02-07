<?php

namespace Drupal\artisan\customizations;

/**
 * Page header - Artisan customizations definition.
 */
trait ArtisanCustomizationsHeaderTrait {

  /**
   * Main definition/s.
   *
   * @return array
   *   Definition.
   */
  protected static function getHeaderDefinitions() {
    return [
      'header' => [
        'wrapper' => 'header',
        'label' => t('Global'),
        'type_default' => 'textfield',
        'selector_default' => ':root',
        'list' => [
          'static' => [
            'label' => t('Static (default sticky)'),
            'type' => 'checkbox',
          ],
          'opacity' => ArtisanCustomizations::getDefaultDefinition('opacity'),
          'height' => ArtisanCustomizations::getDefaultDefinition('height'),
          'color' => ArtisanCustomizations::getDefaultDefinition('color'),
          'background' => ArtisanCustomizations::getDefaultDefinition('background_color'),
          'z_index' => ArtisanCustomizations::getDefaultDefinition('z_index'),
        ],
      ],
    ];
  }

}
