<?php

namespace Drupal\artisan\customizations;

/**
 * Color palette - Artisan customizations definition.
 */
trait ArtisanCustomizationsPaletteTrait {

  /**
   * Main definition/s.
   *
   * @return array
   *   Definition.
   */
  protected static function getPaletteDefinitions() {
    return [
      'palette' => [
        'wrapper' => 'base',
        'label' => t('Palette'),
        'type_default' => 'color',
        'selector_default' => ':root',
        'list' => [
          'branding_primary' => [
            'label' => t('Branding primary'),
          ] + ArtisanCustomizations::getDefaultDefinition('color'),
          'branding_secondary' => [
            'label' => t('Branding secondary'),
          ] + ArtisanCustomizations::getDefaultDefinition('color'),
          'primary' => [
            'label' => t('Primary'),
          ] + ArtisanCustomizations::getDefaultDefinition('color'),
          'secondary' => [
            'label' => t('Secondary'),
          ] + ArtisanCustomizations::getDefaultDefinition('color'),
          'success' => [
            'label' => t('Success'),
          ] + ArtisanCustomizations::getDefaultDefinition('color'),
          'info' => [
            'label' => t('Info'),
          ] + ArtisanCustomizations::getDefaultDefinition('color'),
          'warning' => [
            'label' => t('Warning'),
          ] + ArtisanCustomizations::getDefaultDefinition('color'),
          'danger' => [
            'label' => t('Danger'),
          ] + ArtisanCustomizations::getDefaultDefinition('color'),
          'light' => [
            'label' => t('Light'),
          ] + ArtisanCustomizations::getDefaultDefinition('color'),
          'dark' => [
            'label' => t('Dark'),
          ] + ArtisanCustomizations::getDefaultDefinition('color'),
          'white' => [
            'label' => t('White'),
          ] + ArtisanCustomizations::getDefaultDefinition('color'),
          'black' => [
            'label' => t('Black'),
          ] + ArtisanCustomizations::getDefaultDefinition('color'),
        ],
      ],
    ];
  }

}
