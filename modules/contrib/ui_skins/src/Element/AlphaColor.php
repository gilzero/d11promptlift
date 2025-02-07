<?php

declare(strict_types=1);

namespace Drupal\ui_skins\Element;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Attribute\FormElement;
use Drupal\Core\Render\Element\FormElementBase;

/**
 * Provides a color form element with alpha channel support.
 *
 * Usage example:
 *
 * @code
 * $form['color'] = [
 *   '#type' => 'ui_skins_alpha_color',
 *   '#default_value' => '#00112233',
 * ];
 *
 * @endcode
 */
#[FormElement('ui_skins_alpha_color')]
class AlphaColor extends FormElementBase {

  /**
   * Length when encoding a color channel value in hexadecimal.
   */
  public const HEXADECIMAL_CHANNEL_LENGTH = 2;

  /**
   * Position of the RGB channels in parsing regex.
   */
  public const RGB_CHANNELS_POSITION = 1;

  /**
   * Position of the alpha channel in parsing regex.
   */
  public const ALPHA_CHANNEL_POSITION = 2;

  /**
   * Sie of the number for the alpha element.
   */
  public const ALPHA_ELEMENT_SIZE = 3;

  /**
   * Maximum value of the integer representation of a hexadecimal number.
   */
  public const HEXADECIMAL_INTEGER_MAX = 255;

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = static::class;
    return [
      '#input' => TRUE,
      '#multiple' => FALSE,
      '#default_value' => '',
      '#process' => [
        [$class, 'processAlphaColor'],
        [$class, 'processGroup'],
      ],
      '#element_validate' => [
        [$class, 'validateAlphaColor'],
      ],
      '#theme_wrappers' => ['container'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if (\is_array($input) && isset($input['color'], $input['alpha'])) {
      $alpha = \str_pad(\dechex((int) $input['alpha']), self::HEXADECIMAL_CHANNEL_LENGTH, '0', \STR_PAD_LEFT);
      return $input['color'] . $alpha;
    }

    return $element['#default_value'] ?? NULL;
  }

  /**
   * Processes the alpha color form element.
   *
   * @param array $element
   *   The form element to process.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   *
   * @return array
   *   The processed element.
   */
  public static function processAlphaColor(array &$element, FormStateInterface $form_state, array &$complete_form): array {
    $parsed_value = self::parseValue($element['#default_value']);

    $element['#tree'] = TRUE;
    $element = NestedArray::mergeDeep($element, [
      '#attributes' => [
        'class' => [
          'form--inline',
          'clearfix',
        ],
      ],
    ]);

    $element['color'] = [
      '#type' => 'color',
      '#title' => $element['#title'] ?? \t('Color'),
      '#description' => $element['#description'] ?? '',
      '#default_value' => $parsed_value['color'],
    ];

    $element['alpha'] = [
      '#type' => 'number',
      '#title' => \t('Transparency'),
      '#description' => \t('0 to 255.'),
      '#default_value' => \hexdec($parsed_value['alpha']),
      '#min' => 0,
      '#max' => self::HEXADECIMAL_INTEGER_MAX,
      '#size' => self::ALPHA_ELEMENT_SIZE,
    ];

    return $element;
  }

  /**
   * Form element validation handler for #type 'ui_skins_alpha_color'.
   *
   * Override $form_state value using #element_validate and not #after_build
   * because sub element color would recreate the structure.
   */
  public static function validateAlphaColor(array &$element, FormStateInterface $form_state, array &$complete_form): void {
    $form_state->setValueForElement($element, $element['#value']);
  }

  /**
   * Parse RGBa hexadecimal value to extract alpha channel.
   *
   * @param string $value
   *   The value to parse.
   *
   * @return array
   *   The resulting value,
   */
  protected static function parseValue(string $value): array {
    $parsed_value = [
      'color' => '',
      'alpha' => '',
    ];

    $matches = [];
    if (\preg_match('/^(#[0-9a-fA-F]{6})([0-9a-fA-F]{2})$/', $value, $matches)) {
      $parsed_value['color'] = $matches[self::RGB_CHANNELS_POSITION];
      $parsed_value['alpha'] = $matches[self::ALPHA_CHANNEL_POSITION];
    }

    return $parsed_value;
  }

}
