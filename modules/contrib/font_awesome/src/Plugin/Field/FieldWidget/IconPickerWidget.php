<?php

namespace Drupal\font_awesome\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the 'font_awesome_icon_picker_widget' field widget.
 *
 * @FieldWidget(
 *   id = "font_awesome_icon_picker_widget",
 *   label = @Translation("Font Awesome icon picker"),
 *   field_types = {"string"},
 * )
 */
class IconPickerWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'json_url' => '/modules/contrib/font_awesome/js/iconpicker-1.5.0.json',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $element['json_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path to the set of icons'),
      '#default_value' => $this->getSetting('json_url'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = $this->t('Path to icon set: @json_url', ['@json_url' => $this->getSetting('json_url')]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element['value'] = $element + [
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->value ?? NULL,
      '#attributes' => [
        'class' => ['font-awesome-icon-picker'],
        'data-json-url' => $this->getSetting('json_url'),
      ],
    ];

    $element['#attached']['library'][] = 'font_awesome/iconpicker-widget';

    return $element;
  }

}
