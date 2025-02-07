<?php

namespace Drupal\artisan\customizations;

/**
 * Breadcrumb - Artisan customizations definition.
 */
trait ArtisanCustomizationsBreadcrumbTrait {

  /**
   * Main definition/s.
   *
   * @return array
   *   Definition.
   */
  protected static function getBreadcrumbDefinitions() {
    return [
      'breadcrumb' => [
        'wrapper' => 'breadcrumb',
        'label' => t('Global'),
        'type_default' => 'textfield',
        'selector_default' => '.breadcrumb',
        'list' => [
          'font_family' => ArtisanCustomizations::getDefaultDefinition('font_family'),
          'font_size' => ArtisanCustomizations::getDefaultDefinition('font_size'),
          'font_weight' => ArtisanCustomizations::getDefaultDefinition('font_weight'),
          'padding_x' => ArtisanCustomizations::getDefaultDefinition('padding_x'),
          'padding_y' => ArtisanCustomizations::getDefaultDefinition('padding_y'),
          'margin_y' => ArtisanCustomizations::getDefaultDefinition('margin_y'),
          'divider' => [
            'label' => t('Divider'),
            'type' => 'textfield',
            'description' => "'≫', '/', '>', '\\2192', '˃', '\\21A0', '↠'",
          ],
          'color' => ArtisanCustomizations::getDefaultDefinition('color'),
          'background' => ArtisanCustomizations::getDefaultDefinition('background_color'),
          'accent_color' => ArtisanCustomizations::getDefaultDefinition('accent_color'),
        ],
      ],
    ];
  }

}
