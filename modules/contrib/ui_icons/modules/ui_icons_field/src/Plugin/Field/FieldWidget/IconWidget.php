<?php

declare(strict_types=1);

namespace Drupal\ui_icons_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Attribute\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Theme\Icon\IconDefinitionInterface;

/**
 * Plugin implementation of the 'icon_widget' widget.
 */
#[FieldWidget(
  id: 'icon_widget',
  label: new TranslatableMarkup('Icon'),
  field_types: ['ui_icon'],
)]
class IconWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings(): array {
    return [
      'icon_selector' => 'icon_autocomplete',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
    $element = [];

    $element['icon_selector'] = [
      '#type' => 'select',
      '#title' => $this->t('Icon selector'),
      '#options' => $this->getPickerOptions(),
      '#default_value' => $this->getSetting('icon_selector'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(): array {
    $summary = [];

    $icon_selector = $this->getSetting('icon_selector');
    $summary[] = $this->t('Selector: @type', ['@type' => $this->getPickerOptions()[$icon_selector]]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state): array {
    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();

    /** @var \Drupal\Core\Field\FieldItemInterface $item */
    $item = $items[$delta];

    $icon_selector = $this->getSetting('icon_selector');
    $allowed_icon_pack = array_filter($this->fieldDefinition->getSetting('allowed_icon_pack') ?? []);
    $element['value'] = [
      '#type' => $icon_selector,
      '#title' => $cardinality === 1 ? $this->fieldDefinition->getLabel() : $this->t('Icon'),
      '#allowed_icon_pack' => $allowed_icon_pack,
      '#required' => $element['#required'] ?? FALSE,
      '#show_settings' => FALSE,
      '#default_value' => NULL,
    ];

    if ($item && $item->target_id) {
      $element['value']['#default_value'] = $item->target_id;
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state): array {
    // Only store the icon ID from the FormElement result.
    // @todo #return_id by default?
    foreach ($values as &$item) {
      if (empty($item['value']['icon']) || !$item['value']['icon'] instanceof IconDefinitionInterface) {
        unset($item['value']);
        continue;
      }

      $icon = $item['value']['icon'];
      $item['target_id'] = $icon->getId();
      unset($item['value']);
    }

    return $values;
  }

  /**
   * Get the icon selector options.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup[]
   *   An array of options for selectors options.
   */
  private function getPickerOptions(): array {
    return [
      'icon_autocomplete' => $this->t('Autocomplete'),
      'icon_picker' => $this->t('Picker'),
    ];
  }

}
