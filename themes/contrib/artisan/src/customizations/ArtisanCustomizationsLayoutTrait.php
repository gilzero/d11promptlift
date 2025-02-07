<?php

namespace Drupal\artisan\customizations;

/**
 * Page layout - Artisan customizations definition.
 */
trait ArtisanCustomizationsLayoutTrait {

  /**
   * Main definition/s.
   *
   * @return array
   *   Definition.
   */
  protected static function getLayoutDefinitions() {
    return [
      'layout' => [
        'wrapper' => 'layout',
        'label' => t('Page layout'),
        'type_default' => 'textfield',
        'selector_default' => ':root',
        'list' => [
          'content_max_width' => [
            'label' => t('Content max width'),
            'description' => t('Maximun width content (excluding backgrounds). E.g: :examples.', [
              ':examples' => ArtisanCustomizations::MAX_WIDTH_EXAMPLE,
            ]),
            'extra_widget' => 'numeric_unit',
          ],
          'global_max_width' => [
            'label' => t('Global max width'),
            'description' => t('Maximun width as page limit (including backgrounds). E.g: :examples.', [
              ':examples' => ArtisanCustomizations::MAX_WIDTH_EXAMPLE,
            ]),
            'extra_widget' => 'numeric_unit',
          ],
          'spacing' => [
            'label' => t('Spacing (gutter, container padding, main spacer,...)'),
            'description' => ArtisanCustomizations::PADDING_EXAMPLE,
            'extra_widget' => 'numeric_unit',
          ],
        ],
      ],
    ];
  }

}
