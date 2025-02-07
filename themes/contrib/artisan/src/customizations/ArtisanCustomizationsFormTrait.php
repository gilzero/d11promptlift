<?php

namespace Drupal\artisan\customizations;

/**
 * Forms - Artisan customizations definition.
 */
trait ArtisanCustomizationsFormTrait {

  /**
   * Main definition/s.
   *
   * @return array
   *   Definition.
   */
  protected static function getFormDefinitions() {
    $definitions = [
      'form' => [
        'wrapper' => 'form',
        'label' => t('Global'),
        'wrapper_description' => t('Textfield, email, number, textarea, checkbox, radio.'),
        'type_default' => 'textfield',
        'selector_default' => 'form',
        'list' => [
          'element_margin_bottom' => [
            'label' => t('Form element bottom spacing'),
            'description' => ArtisanCustomizations::MARGIN_EXAMPLE,
            'extra_widget' => 'numeric_unit',
          ],
          'element_floating_label' => [
            'label' => t('Set floating labels (checkboxes, radios, files & textareas excluded)'),
            'type' => 'checkbox',
          ],
          'element_switches' => [
            'label' => t('Use switches (checkboxes and radios only)'),
            'type' => 'checkbox',
          ],
          'element_inline' => [
            'label' => t('Inline when multiple (checkboxes and radios only)'),
            'type' => 'checkbox',
          ],
        ],
      ],
    ];
    $definitions += static::getFormInputDefinitions();
    $definitions += static::getFormLabelDefinitions();
    return $definitions;
  }

  /**
   * Form input definitions.
   *
   * @return array
   *   Definitions.
   */
  protected static function getFormInputDefinitions() {
    return [
      'form_input' => [
        'wrapper' => 'form',
        'label' => t('Input'),
        'wrapper_description' => t('Textfield, email, number, textarea, checkbox, radio.'),
        'type_default' => 'textfield',
        'selector_default' => 'form',
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
          'shadow' => ArtisanCustomizations::getDefaultDefinition('shadow'),
          'color' => ArtisanCustomizations::getDefaultDefinition('color'),
          'placeholder_color' => ArtisanCustomizations::getDefaultDefinition('placeholder_color'),
          'background' => ArtisanCustomizations::getDefaultDefinition('background_color'),
          'border_color' => ArtisanCustomizations::getDefaultDefinition('border_color'),
          'disabled_background' => [
            'label' => t('Disabled background color'),
          ] + ArtisanCustomizations::getDefaultDefinition('background_color'),
          'accent_color' => ArtisanCustomizations::getDefaultDefinition('accent_color'),
          'accent_background' => ArtisanCustomizations::getDefaultDefinition('accent_background_color'),
          'accent_border_color' => ArtisanCustomizations::getDefaultDefinition('accent_border_color'),
        ],
      ],
    ];
  }

  /**
   * Form label definitions.
   *
   * @return array
   *   Definitions.
   */
  protected static function getFormLabelDefinitions() {
    return [
      'form_label' => [
        'wrapper' => 'form',
        'label' => t('Label'),
        'wrapper_description' => t('Textfield, email, number, textarea, checkbox, radio.'),
        'type_default' => 'textfield',
        'selector_default' => 'form',
        'list' => [
          'font_family' => ArtisanCustomizations::getDefaultDefinition('font_family'),
          'font_size' => ArtisanCustomizations::getDefaultDefinition('font_size'),
          'font_weight' => ArtisanCustomizations::getDefaultDefinition('font_weight'),
          'line_height' => ArtisanCustomizations::getDefaultDefinition('line_height'),
          'margin_bottom' => [
            'label' => t('Bottom outer spacing'),
          ] + ArtisanCustomizations::getDefaultDefinition('margin'),
        ],
      ],
    ];
  }

}
