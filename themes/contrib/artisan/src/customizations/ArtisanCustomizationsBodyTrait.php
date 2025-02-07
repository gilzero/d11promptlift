<?php

namespace Drupal\artisan\customizations;

/**
 * Body - Artisan customizations definition.
 */
trait ArtisanCustomizationsBodyTrait {

  /**
   * Main definition/s.
   *
   * @return array
   *   Definition.
   */
  protected static function getBodyDefinitions() {
    return [
      'body' => [
        'wrapper' => 'layout',
        'label' => t('Body'),
        'type_default' => 'textfield',
        'selector_default' => ':root',
        'list' => [
          'color' => ArtisanCustomizations::getDefaultDefinition('color'),
          'background' => ArtisanCustomizations::getDefaultDefinition('background_color'),
        ],
      ],
    ];
  }

}
