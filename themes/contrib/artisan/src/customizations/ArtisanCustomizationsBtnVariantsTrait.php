<?php

namespace Drupal\artisan\customizations;

use Drupal\Component\Utility\Html;

/**
 * Button variants - Artisan customizations definition.
 */
trait ArtisanCustomizationsBtnVariantsTrait {

  /**
   * Variants list to generate definition/s.
   *
   * @return array
   *   Variants list.
   */
  protected static function getBtnVariantsList() {
    return [
      'primary' => [
        'btn' => t('Button primary'),
        'btn_outline' => t('Button primary outline'),
      ],
      'secondary' => [
        'btn' => t('Button secondary'),
        'btn_outline' => t('Button secondary outline'),
      ],
      'success' => [
        'btn' => t('Button success'),
        'btn_outline' => t('Button success outline'),
      ],
      'danger' => [
        'btn' => t('Button danger'),
        'btn_outline' => t('Button danger outline'),
      ],
      'warning' => [
        'btn' => t('Button warning'),
        'btn_outline' => t('Button warning outline'),
      ],
      'info' => [
        'btn' => t('Button info'),
        'btn_outline' => t('Button info outline'),
      ],
      'light' => [
        'btn' => t('Button light'),
        'btn_outline' => t('Button light outline'),
      ],
      'dark' => [
        'btn' => t('Button dark'),
        'btn_outline' => t('Button dark outline'),
      ],
      'link' => [
        'btn' => t('Button link'),
      ],
    ];
  }

  /**
   * Main definition/s.
   *
   * @return array
   *   Definition.
   */
  protected static function getBtnVariantsDefinitions() {
    $definitions = [];
    foreach (static::getBtnVariantsList() as $variant_delta => $variants) {
      foreach ($variants as $sub_variant_delta => $sub_variant_label) {
        $definitions[$sub_variant_delta . '_' . $variant_delta] = [
          'wrapper' => 'buttons',
          'label' => $sub_variant_label,
          'type_default' => 'color',
          'selector_default' => '.' . Html::getClass($sub_variant_delta . '-' . $variant_delta),
          'list' => [
            'color' => ArtisanCustomizations::getDefaultDefinition('color'),
            // Outline variants do not allow set background, link either.
            'background' => $sub_variant_delta === 'btn' && $variant_delta !== 'link' ? ArtisanCustomizations::getDefaultDefinition('background_color') : [],
            // Link do not use this.
            'border_color' => $variant_delta !== 'link' ? ArtisanCustomizations::getDefaultDefinition('border_color') : [],
            'accent_color' => ArtisanCustomizations::getDefaultDefinition('accent_color'),
            // Link do not use this.
            'accent_background' => $variant_delta !== 'link' ? ArtisanCustomizations::getDefaultDefinition('accent_background_color') : [],
            // Link do not use this.
            'accent_border_color' => $variant_delta !== 'link' ? ArtisanCustomizations::getDefaultDefinition('accent_border_color') : [],
          ],
        ];
      }
    }
    return $definitions;
  }

}
