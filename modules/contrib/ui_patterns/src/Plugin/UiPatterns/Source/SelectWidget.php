<?php

declare(strict_types=1);

namespace Drupal\ui_patterns\Plugin\UiPatterns\Source;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\ui_patterns\Attribute\Source;
use Drupal\ui_patterns\EnumTrait;
use Drupal\ui_patterns\SourcePluginPropValue;

/**
 * Plugin implementation of the source.
 */
#[Source(
  id: 'select',
  label: new TranslatableMarkup('Select'),
  description: new TranslatableMarkup('A drop-down menu or scrolling selection box.'),
  prop_types: ['enum', 'variant'],
  tags: ['widget']
)]
class SelectWidget extends SourcePluginPropValue {

  use EnumTrait;

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
    $form = parent::settingsForm($form, $form_state);
    $form['value'] = [
      '#type' => 'select',
      '#default_value' => $this->getSetting('value'),
      "#options" => static::getEnumOptions($this->propDefinition),
      "#empty_option" => $this->t("- Select -"),
    ];
    $this->addRequired($form['value']);
    // With Firefox, autocomplete may override #default_value.
    // https://drupal.stackexchange.com/questions/257732/default-value-not-working-in-select-field
    $form['value']['#attributes']['autocomplete'] = 'off';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getPropValue(): mixed {
    $value = $this->getSetting('value');
    if (empty($value)) {
      return $value;
    }
    $enum = $this->propDefinition['enum'] ?? [];
    return $this->convertValueToEnumType($value, $enum);
  }

}
