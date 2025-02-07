<?php

namespace Drupal\artisan\customizations;

/**
 * Buttons - Artisan customizations definition.
 */
trait ArtisanCustomizationsBtnTrait {

  /**
   * Main definition/s.
   *
   * @return array
   *   Definition.
   */
  protected static function getBtnDefinitions() {
    return [
      'btn' => [
        'wrapper' => 'buttons',
        'label' => t('Global'),
        'type_default' => 'textfield',
        'selector_default' => '.btn',
        'list' => [
          'border_width' => ArtisanCustomizations::getDefaultDefinition('border_width'),
          'border_radius' => ArtisanCustomizations::getDefaultDefinition('border_radius'),
          'font_weight' => ArtisanCustomizations::getDefaultDefinition('font_weight'),
          'padding_x' => ArtisanCustomizations::getDefaultDefinition('padding_x'),
          'padding_x_sm' => ArtisanCustomizations::getDefaultDefinition('padding_x_sm'),
          'padding_x_lg' => ArtisanCustomizations::getDefaultDefinition('padding_x_lg'),
          'padding_y' => ArtisanCustomizations::getDefaultDefinition('padding_y'),
          'padding_y_sm' => ArtisanCustomizations::getDefaultDefinition('padding_y_sm'),
          'padding_y_lg' => ArtisanCustomizations::getDefaultDefinition('padding_y_lg'),
          'line_height' => ArtisanCustomizations::getDefaultDefinition('line_height'),
          'font_family' => ArtisanCustomizations::getDefaultDefinition('font_family'),
          'font_size' => ArtisanCustomizations::getDefaultDefinition('font_size'),
          'font_size_sm' => ArtisanCustomizations::getDefaultDefinition('font_size_sm'),
          'font_size_lg' => ArtisanCustomizations::getDefaultDefinition('font_size_lg'),
          'disabled_opacity' => [
            'label' => t('Disabled opacity'),
          ] + ArtisanCustomizations::getDefaultDefinition('opacity'),
          'shadow' => ArtisanCustomizations::getDefaultDefinition('shadow'),
        ],
      ],
    ];
  }

}
