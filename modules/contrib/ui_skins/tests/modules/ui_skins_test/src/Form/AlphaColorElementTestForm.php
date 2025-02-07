<?php

declare(strict_types=1);

namespace Drupal\ui_skins_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form used in tests.
 */
class AlphaColorElementTestForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ui_skins_test_alpha_color';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['alpha_color'] = [
      '#type' => 'ui_skins_alpha_color',
      '#title' => $this->t('My color'),
      '#description' => $this->t('My alpha color.'),
      '#default_value' => '#0d6efdff',
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $color = $form_state->getValue('alpha_color');
    $this->messenger()->addMessage($this->t('You specified a color of %color.', ['%color' => $color]));
  }

}
