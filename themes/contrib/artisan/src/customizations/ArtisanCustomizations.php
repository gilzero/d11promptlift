<?php

namespace Drupal\artisan\customizations;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;

/**
 * Artisan customizations.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ArtisanCustomizations implements ArtisanCustomizationsInterface {

  const VALID_TYPES = ['color', 'textfield', 'number', 'checkbox'];
  const VALID_EXTRA_WIDGETS = ['numeric_input', 'numeric_unit', 'font_weight', 'decoration'];

  use ArtisanCustomizationsPaletteTrait;
  use ArtisanCustomizationsBaseFontTrait;
  use ArtisanCustomizationsLinksTrait;
  use ArtisanCustomizationsBodyTrait;
  use ArtisanCustomizationsHeadingsTrait;
  use ArtisanCustomizationsHxTrait;
  use ArtisanCustomizationsLayoutTrait;
  use ArtisanCustomizationsHeaderTrait;
  use ArtisanCustomizationsHeaderLinksTrait;
  use ArtisanCustomizationsHeaderTopTrait;
  use ArtisanCustomizationsHeaderTopLinksTrait;
  use ArtisanCustomizationsFooterTrait;
  use ArtisanCustomizationsFooterLinksTrait;
  use ArtisanCustomizationsFooterTopTrait;
  use ArtisanCustomizationsFooterTopLinksTrait;
  use ArtisanCustomizationsResponsiveAccentTrait;
  use ArtisanCustomizationsBtnTrait;
  use ArtisanCustomizationsBtnVariantsTrait;
  use ArtisanCustomizationsFormTrait;
  use ArtisanCustomizationsBreadcrumbTrait;
  use ArtisanCustomizationsDisplayxTrait;

  const COLOR_EMPTY = '#000001';
  const FONT_SIZE_EXAMPLE = '16px, 1rem, 1em, calc(1.625rem + 4.5vw)';
  const FONT_WEIGHT_EXAMPLE = '100, ..., 700, lighter, ..., bolder';
  const FONT_FAMILY_EXAMPLE = 'Roboto, Arima, system-ui';
  const LINE_HEIGHT_EXAMPLE = '1, 1.5';
  const Z_INDEX_EXAMPLE = '100, 500, 1000';
  const BORDER_RADIUS_EXAMPLE = '.5em, 1rem, 50%';
  const BORDER_WIDTH_EXAMPLE = '1px, .2em, .3rem';
  const PADDING_EXAMPLE = '10px, 1em, 1rem';
  const MARGIN_EXAMPLE = '10px, 1em, 1rem';
  const OPACITY_EXAMPLE = '0, 0.5, 1';
  const DECORATION_EXAMPLE = 'underline, line-through';
  const HEIGHT_EXAMPLE = '100px, 10em, 10rem';
  const MAX_WIDTH_EXAMPLE = '1920px, 120rem, 120em';
  const BOX_SHADOW_EXAMPLE = '0 .5rem 1rem rgba(0,0,0,.15)';
  const DARK_MODE_DEFINITIONS_REGEX = '/(?:color|palette|background)/i';

  /**
   * {@inheritdoc}
   */
  public static function getDefinitions() {
    // Use dedicated plugin definitions intead? Let's hold that until sdc
    // "design tokens" is clarified, ready to use & working.
    $definitions = [];
    $definitions += self::getPaletteDefinitions();
    $definitions += self::getBaseFontDefinitions();
    $definitions += self::getLinksDefinitions();
    $definitions += self::getBodyDefinitions();
    $definitions += self::getLayoutDefinitions();
    $definitions += self::getHeaderDefinitions();
    $definitions += self::getHeaderLinksDefinitions();
    $definitions += self::getHeaderTopDefinitions();
    $definitions += self::getHeaderTopLinksDefinitions();
    $definitions += self::getResponsiveAccentDefinitions();
    $definitions += self::getFooterDefinitions();
    $definitions += self::getFooterLinksDefinitions();
    $definitions += self::getFooterTopDefinitions();
    $definitions += self::getFooterTopLinksDefinitions();
    $definitions += self::getBreadcrumbDefinitions();
    $definitions += self::getHeadingsDefinitions();
    $definitions += self::getHxDefinitions();
    $definitions += self::getDisplayxDefinitions();
    $definitions += self::getBtnDefinitions();
    $definitions += self::getBtnVariantsDefinitions();
    $definitions += self::getFormDefinitions();

    \Drupal::service('module_handler')->alter('artisan_customizations', $definitions);
    \Drupal::service('theme.manager')->alter('artisan_customizations', $definitions);

    self::applyDarkModeDefinitions($definitions);
    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaultDefinition(string $default_definition_key) {
    $color_default = [
      'label' => t('Color'),
      'type' => 'color',
    ];
    $padding_default = [
      'label' => t('Inner spacing'),
      'description' => self::PADDING_EXAMPLE,
      'type' => 'textfield',
      'extra_widget' => 'numeric_unit',
    ];
    $margin_default = [
      'label' => t('Outer spacing'),
      'description' => self::MARGIN_EXAMPLE,
      'type' => 'textfield',
      'extra_widget' => 'numeric_unit',
    ];
    $font_size_default = [
      'label' => t('Font size'),
      'description' => self::FONT_SIZE_EXAMPLE,
      'type' => 'textfield',
      'extra_widget' => 'numeric_unit',
    ];
    $defaults = [
      'color' => $color_default,
      'accent_color' => [
        'label' => t('Accent color'),
      ] + $color_default,
      'placeholder_color' => [
        'label' => t('Placeholder color'),
      ] + $color_default,
      'background_color' => [
        'label' => t('Background color'),
      ] + $color_default,
      'accent_background_color' => [
        'label' => t('Accent background color'),
      ] + $color_default,
      'border_color' => [
        'label' => t('Border color'),
      ] + $color_default,
      'accent_border_color' => [
        'label' => t('Accent border color'),
      ] + $color_default,
      'font_size' => $font_size_default,
      'font_size_sm' => [
        'label' => t('Font size (small/medium screens or variant)'),
      ] + $font_size_default,
      'font_size_lg' => [
        'label' => t('Font size (large screens or variant)'),
      ] + $font_size_default,
      'font_weight' => [
        'label' => t('Font weight'),
        'description' => self::FONT_WEIGHT_EXAMPLE,
        'type' => 'textfield',
        'extra_widget' => 'font_weight',
      ],
      'font_family' => [
        'label' => t('Font family'),
        'description' => self::FONT_FAMILY_EXAMPLE,
        'type' => 'textfield',
      ],
      'line_height' => [
        'label' => t('Line height'),
        'description' => self::LINE_HEIGHT_EXAMPLE,
        'type' => 'textfield',
        'extra_widget' => 'numeric_input',
      ],
      'z_index' => [
        'label' => t('Z Index'),
        'description' => self::Z_INDEX_EXAMPLE,
        'type' => 'number',
      ],
      'padding' => $padding_default,
      'padding_x' => [
        'label' => t('Horizontal inner spacing'),
      ] + $padding_default,
      'padding_x_sm' => [
        'label' => t('Horizontal inner spacing (small/medium screens or variant)'),
      ] + $padding_default,
      'padding_x_lg' => [
        'label' => t('Horizontal inner spacing (large screens or variant)'),
      ] + $padding_default,
      'padding_y' => [
        'label' => t('Vertical inner spacing'),
      ] + $padding_default,
      'padding_y_sm' => [
        'label' => t('Vertical inner spacing (small/medium screens or variant)'),
      ] + $padding_default,
      'padding_y_lg' => [
        'label' => t('Vertical inner spacing (large screens or variant)'),
      ] + $padding_default,
      'margin' => $margin_default,
      'margin_x' => [
        'label' => t('Horizontal outer spacing'),
      ] + $margin_default,
      'margin_y' => [
        'label' => t('Vertical outer spacing'),
      ] + $margin_default,
      'border_width' => [
        'label' => t('Border width'),
        'description' => self::BORDER_WIDTH_EXAMPLE,
        'type' => 'textfield',
        'extra_widget' => 'numeric_unit',
      ],
      'border_radius' => [
        'label' => t('Border radius'),
        'description' => self::BORDER_RADIUS_EXAMPLE,
        'type' => 'textfield',
        'extra_widget' => 'numeric_unit',
      ],
      'opacity' => [
        'label' => t('Opacity'),
        'description' => self::OPACITY_EXAMPLE,
        'type' => 'textfield',
        'extra_widget' => 'numeric_input',
      ],
      'shadow' => [
        'label' => t('Box shadow'),
        'description' => self::BOX_SHADOW_EXAMPLE,
        'type' => 'textfield',
      ],
      'decoration' => [
        'label' => t('Decoration'),
        'description' => ArtisanCustomizations::DECORATION_EXAMPLE,
        'type' => 'textfield',
        'extra_widget' => 'decoration',
      ],
      'height' => [
        'label' => t('Height'),
        'description' => self::HEIGHT_EXAMPLE,
        'type' => 'textfield',
        'extra_widget' => 'numeric_unit',
      ],
    ];
    return $defaults[$default_definition_key] ?? [];
  }

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   */
  public static function getAttachmentStyles() {
    $style_value = '';
    $verbose = Settings::get('artisan_customizations_verbose', FALSE);
    $by_selector = self::getStylesGroupedBySelector($verbose, FALSE);
    $dark_mode = theme_get_setting('dark_mode') ?? FALSE;
    $by_selector_dark_mode = $dark_mode ? self::getStylesGroupedBySelector($verbose, TRUE) : [];
    $end_of_line = $verbose ? PHP_EOL : '';

    foreach ($by_selector as $selector => $css_vars) {
      $style_value .= $selector . '{' . $end_of_line;
      foreach ($css_vars as $css_var_name => $css_var_value) {
        $style_value .= !empty($css_var_value) ? $css_var_name . ':' . $css_var_value . ';' . $end_of_line : '/*' . $css_var_name . '*/' . $end_of_line;
      }
      $style_value .= '}' . $end_of_line;
    }
    if (!empty($by_selector_dark_mode)) {
      $style_value .= '@media (prefers-color-scheme: dark) {' . $end_of_line;
    }
    foreach ($by_selector_dark_mode as $selector => $css_vars) {
      $style_value .= $selector . '{' . $end_of_line;
      foreach ($css_vars as $css_var_name => $css_var_value) {
        $style_value .= !empty($css_var_value) ? $css_var_name . ':' . $css_var_value . ';' . $end_of_line : '/*' . $css_var_name . '*/' . $end_of_line;
      }
      $style_value .= '}' . $end_of_line;
    }
    if (!empty($by_selector_dark_mode)) {
      $style_value .= '}' . $end_of_line;
    }
    return [
      [
        '#type' => 'html_tag',
        '#tag' => 'style',
        '#value' => $style_value,
        '#access' => !empty($style_value),
        '#attributes' => ['data-artisan-customizations' => 'enabled'],
      ],
      'artisan_customization_styles',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function getAttachmentStylesPreview() {
    $style_value = '';
    $end_of_line = PHP_EOL;
    $by_selector = [];
    foreach (self::getDefinitions() as $group_delta => $group) {
      foreach ($group['list'] as $customization_delta => $customization) {
        $selector = $customization['selector'] ?? $group['selector_default'];
        $css_var_name_application = '--' . Html::getClass($group_delta . '-' . $customization_delta);
        $css_var_name = '--theme-' . Html::getClass($group_delta . '-' . $customization_delta);
        $css_var_value = theme_get_setting($group_delta . '_' . $customization_delta);
        if (empty($customization) || str_contains($customization_delta, '__dark_mode')) {
          continue;
        }
        $by_selector[$selector][$css_var_name] = [
          'value' => $css_var_value,
          'label' => static::getThemeSettingsFormElementsWrapperLabel($group['wrapper'] ?? 'component') . ' - ' . $group['label'] . ': ' . $customization['label'],
          'aplication_def' => $css_var_name_application . ': var(' . $css_var_name . ', var(--FALLBACK-CSS-VAR, FALLBACK-CSS-VALUE));',
          'aplication' => 'CSS-PROPERTY: var(' . $css_var_name_application . ');',
        ];
      }
    }

    foreach ($by_selector as $selector => $css_vars) {
      $style_value .= $selector . ' {' . $end_of_line;
      foreach ($css_vars as $css_var_name => $css_var) {
        $style_value .= $end_of_line . '  /*   ' . $css_var['label'] . '   */' . $end_of_line;
        $style_value .= !empty($css_var['value']) ? '  ' . $css_var_name . ': ' . $css_var['value'] . ';' . $end_of_line : '  /* ' . $css_var_name . ' */' . $end_of_line;
        $style_value .= $end_of_line . '  /*   ' . t('Usage suggestion of: %label', [
          '%label' => $css_var['label'],
          // phpcs:ignore Drupal.Semantics.FunctionT.ConcatString
        ]) . ' */' . $end_of_line;
        $style_value .= '  /* ' . $css_var['aplication_def'] . ' */' . $end_of_line;
        $style_value .= '  /* ' . $css_var['aplication'] . ' */' . $end_of_line;
        $style_value .= $end_of_line;
      }
      $style_value .= '}' . $end_of_line . $end_of_line;
    }
    return [
      '#type' => 'html_tag',
      '#tag' => 'pre',
      '#value' => $style_value,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function getThemeSettingsFormElements() {
    $elements = [];
    $definitions = ArtisanCustomizations::getDefinitions();

    $elements['dark_mode'] = [
      '#type' => 'checkbox',
      '#title' => t('Artisan - Enable dark mode customizations'),
      '#weight' => -999,
      '#default_value' => theme_get_setting('dark_mode') ?? FALSE,
      '#description' => t('Check this to expose & customize alternative dark mode palette & colors. Save after enable / disable to confirm.'),
    ];

    $elements['presets'] = [
      '#type' => 'textarea',
      '#title' => t('Artisan - Customizations preset'),
      '#wrapper_attributes' => ['class' => ['artisan-theme-form-presets-wrapper']],
      '#attributes' => [
        'class' => [
          'visually-hidden',
          'artisan-theme-form-presets',
        ],
      ],
      '#weight' => -999,
      '#default_value' => theme_get_setting('presets'),
      '#description' => t('Several grouped customizations of your choice, that can be applied with just one click. Use this to store all current configurations in a preset then switch easily and/or export or import manually. Import / export @here by JSON text (save to apply changes when modified).', ['@here' => Link::fromTextAndUrl(t('here'), Url::fromUserInput('#customizations'))->toString()]),
    ];

    $elements['customizations'] = [
      '#type' => 'vertical_tabs',
      '#title' => t('Artisan - Customizations'),
      '#description' => t('Adjust each theme available option of your choice, you can apply a preset, adjust whatever and generate a new one. These customizations will be available as css variables under :root selector or specific one according to definition.'),
      '#attributes' => ['class' => ['artisan-theme-form-customizations']],
      '#weight' => -999,
    ];

    foreach ($definitions as $group_delta => $group) {
      if (empty($group)) {
        continue;
      }
      $wrapper = $group['wrapper'] ?? 'component';
      $wraper_label = static::getThemeSettingsFormElementsWrapperLabel($wrapper);
      $elements['customizations'][$wrapper] = $elements['customizations'][$wrapper] ?? [
        '#type' => 'details',
        '#title' => $wraper_label,
        '#attributes' => [
          'class' => [
            'artisan-theme-form-group-wrapper',
            'artisan-theme-form-group-wrapper--' . Html::getClass($wrapper),
          ],
        ],
        '#open' => FALSE,
        '#description' => $group['wrapper_description'] ?? NULL,
        '#group' => 'customizations',
      ];
      $elements['customizations'][$wrapper][$group_delta] = $elements['customizations'][$wrapper][$group_delta] ?? [
        '#type' => 'details',
        '#title' => $group['label'],
        '#attributes' => [
          'class' => [
            'artisan-theme-form-group',
            'artisan-theme-form-group--' . Html::getClass($group_delta),
          ],
        ],
        '#open' => TRUE,
        '#description' => $group['description'] ?? NULL,
      ];
      foreach ($group['list'] as $customization_delta => $customization) {
        if (empty($customization) || empty($customization['label'])) {
          continue;
        }
        $type = $customization['type'] ?? $group['type_default'];
        $extra_widget = $customization['extra_widget'] ?? ($group['extra_widget_default'] ?? NULL);
        if (empty($type) || !in_array($type, static::VALID_TYPES)) {
          continue;
        }
        $default_value = theme_get_setting($group_delta . '_' . $customization_delta);
        $elements['customizations'][$wrapper][$group_delta]['list'][$group_delta . '_' . $customization_delta] = [
          '#type' => $type,
          '#title' => $customization['label'],
          '#default_value' => $default_value,
          '#description' => $customization['description'] ?? NULL,
          '#placeholder' => $customization['placeholder'] ?? NULL,
        ];
        if (!empty($extra_widget) && in_array($extra_widget, self::VALID_EXTRA_WIDGETS)) {
          $elements['customizations'][$wrapper][$group_delta]['list'][$group_delta . '_' . $customization_delta]['#attributes']['class'][] = 'artisan-theme-extra-widget-' . Html::getClass($extra_widget);
        }
        self::themeSettingsFormElementExtra($elements['customizations'][$wrapper][$group_delta]['list'][$group_delta . '_' . $customization_delta]);
      }
    }

    $elements['preview'] = [
      '#type' => 'details',
      '#title' => t('Artisan - Customizations CSS variables preview'),
      '#description' => t('Save to apply current changes and update CSS variables preview. Actual page preview just navigate frontpage & others, no need to compile theme. Those between /* ... */ has no value defined. Note usage suggestion for each customization. Commented lines will be omitted into embeded variable styles.'),
      '#wrapper_attributes' => ['class' => ['artisan-theme-form-presets-preview']],
      '#weight' => -999,
      '#open' => FALSE,
    ];
    $elements['preview']['css'] = static::getAttachmentStylesPreview();
    return $elements;
  }

  /**
   * Apply dark mode definitions.
   *
   * @param array $definitions
   *   Definitions.
   */
  protected static function applyDarkModeDefinitions(array &$definitions) {
    if (theme_get_setting('dark_mode') ?? FALSE) {
      foreach ($definitions as $group_delta => $definition) {
        foreach (array_keys($definition['list'] ?? []) as $definition_delta) {
          if (preg_match(self::DARK_MODE_DEFINITIONS_REGEX, $group_delta . '_' . $definition_delta)) {
            $definitions[$group_delta]['list'][$definition_delta . '__dark_mode'] = $definitions[$group_delta]['list'][$definition_delta];
            if (empty($definitions[$group_delta]['list'][$definition_delta])) {
              continue;
            }
            $definitions[$group_delta]['list'][$definition_delta . '__dark_mode']['label'] = (string) $definitions[$group_delta]['list'][$definition_delta . '__dark_mode']['label'] . ' -- ' . (string) t('Dark mode');
          }
        }
      }
    }
  }

  /**
   * Get theme settings form elements wrapper label.
   *
   * @param string $wrapper
   *   Wrapper key.
   *
   * @return string
   *   Translatable label for passed wrapper key or "Components" fallback.
   */
  protected static function getThemeSettingsFormElementsWrapperLabel($wrapper) {
    $wrappers = [
      'base' => t('Base'),
      'headings' => t('Headings'),
      'displays' => t('Display headings'),
      'breadcrumb' => t('Breadcrumb'),
      'buttons' => t('Buttons'),
      'layout' => t('Page layout'),
      'header' => t('Page header'),
      'footer' => t('Page footer'),
      'responsive' => t('Page responsive'),
      'form' => t('Forms'),
      'component' => t('Components'),
    ];
    return $wrappers[$wrapper] ?? $wrappers['component'];
  }

  /**
   * Theme settings color validate.
   *
   * @param array $element
   *   The field overrides form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the entire form.
   */
  public static function themeSettingsColorValidate(array $element, FormStateInterface $form_state) {
    $hex_color = $form_state->getValue($element['#parents']);
    if (!empty($hex_color) && $hex_color === static::COLOR_EMPTY) {
      $form_state->setValue($element['#parents'], NULL);
    }
  }

  /**
   * Theme settings form element extra.
   *
   * @param array $element
   *   Form element.
   */
  protected static function themeSettingsFormElementExtra(array &$element) {
    // HTML5 color widget does not manage "empty" state so use specific hex
    // color to manage that simulated "empty" state.
    if (($element['#type'] ?? NULL) === 'color') {
      $element['#default_value'] = empty($element['#default_value'] ?? NULL) ? static::COLOR_EMPTY : $element['#default_value'];
      $element['#element_validate'][] = [static::class, 'themeSettingsColorValidate'];
      $element['#attributes']['data-empty'] = static::COLOR_EMPTY;
    }
  }

  /**
   * Get styles grouped by selector.
   *
   * @param bool $verbose
   *   Verbose or minified "$settings['artisan_customizations_verbose'] = TRUE".
   * @param bool $dark_mode
   *   Dark mode or default.
   *
   * @return array
   *   Customization styles from definitions grouped per selector.
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   */
  protected static function getStylesGroupedBySelector(bool $verbose, bool $dark_mode) {
    $definitions = self::getDefinitions();
    $by_selector = [];
    foreach ($definitions as $group_delta => $group) {
      foreach ($group['list'] as $customization_delta => $customization) {
        $dark_mode_customization = str_contains($customization_delta, '__dark_mode');
        // Get just dark mode customizations when requested or default ones.
        if ($dark_mode_customization && !$dark_mode) {
          continue;
        }
        elseif (!$dark_mode_customization && $dark_mode) {
          continue;
        }
        $selector = $customization['selector'] ?? $group['selector_default'];
        $css_var_name = '--theme-' . Html::getClass($group_delta . '-' . $customization_delta);
        if ($dark_mode) {
          $css_var_name = str_replace('__dark-mode', '', $css_var_name);
        }
        $css_var_value = theme_get_setting($group_delta . '_' . $customization_delta);
        if (!$verbose && empty($css_var_value)) {
          continue;
        }
        $by_selector[$selector][$css_var_name] = $css_var_value;
      }
    }
    return $by_selector;
  }

}
