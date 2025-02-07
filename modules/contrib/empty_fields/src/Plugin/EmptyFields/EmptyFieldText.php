<?php

namespace Drupal\empty_fields\Plugin\EmptyFields;

use Drupal\Core\Form\FormStateInterface;
use Drupal\empty_fields\EmptyFieldPluginBase;

/**
 * Defines EmptyFieldText.
 *
 * @EmptyField(
 *   id = "text",
 *   label = @Translation("Display custom text")
 * )
 */
class EmptyFieldText extends EmptyFieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function react(array $context) {
    $args = [
      $context['entity']->getEntityTypeId() => $context['entity'],
      'user' => \Drupal::currentUser(),
    ];
    $text = \Drupal::token()
      ->replace($this->configuration['empty_text'], $args, ['clear' => TRUE]);
    return ['#markup' => $text];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['empty_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Display Custom Text'),
      '#default_value' => $this->configuration['empty_text'],
      '#description' => $this->t('Display text if the field is empty.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    return $this->t('Empty Text: @empty_text', ['@empty_text' => $this->configuration['empty_text']]);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'empty_text' => '',
    ] + parent::defaultConfiguration();
  }

}
