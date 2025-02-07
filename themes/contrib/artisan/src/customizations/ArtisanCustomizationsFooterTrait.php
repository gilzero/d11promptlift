<?php

namespace Drupal\artisan\customizations;

/**
 * Page footer - Artisan customizations definition.
 */
trait ArtisanCustomizationsFooterTrait {

  /**
   * Main definition/s.
   *
   * @return array
   *   Definition.
   */
  protected static function getFooterDefinitions() {
    return [
      'footer' => [
        'wrapper' => 'footer',
        'label' => t('Global'),
        'type_default' => 'color',
        'selector_default' => ':root',
        'list' => [
          'padding_y' => ['label' => t('Inner vertical spacing (top & bottom)')] + ArtisanCustomizations::getDefaultDefinition('padding_y'),
          'color' => ArtisanCustomizations::getDefaultDefinition('color'),
          'background' => ArtisanCustomizations::getDefaultDefinition('background_color'),
        ],
      ],
    ];
  }

}
