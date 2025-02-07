<?php

namespace Drupal\pwa_a2hs\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an Add to Home Screen block.
 *
 * @Block(
 *  id = "pwa_add_to_home_screen",
 *  admin_label = @Translation("PWA Add to Home Screen"),
 *  category = @Translation("PWA"),
 * )
 */
class AddToHomeScreenBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'button_text' => 'Install app',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['intro_text'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Introduction text'),
      '#format' => $this->configuration['intro_text']['format'],
      '#default_value' => $this->configuration['intro_text']['value'],
      '#weight' => '1',
    ];
    $form['button_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button text'),
      '#default_value' => $this->configuration['button_text'],
      '#weight' => '2',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['intro_text'] = $form_state->getValue('intro_text');
    $this->configuration['button_text'] = $form_state->getValue('button_text');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#attached' => [
        'library' => ['pwa_a2hs/pwa_a2hs_prompt'],
        'drupalSettings' => [
          'pwaA2hs' => [
            'pwaA2hsPrompt' => [
              'button_text' => $this->configuration['button_text'],
            ],
          ],
        ],
      ],
      '#theme' => 'pwa_add_to_home_screen',
      '#intro_text' => [
        '#type' => 'processed_text',
        '#text' => $this->configuration['intro_text']['value'],
        '#format' => $this->configuration['intro_text']['format'],
      ],
      '#button_text' => [
        '#markup' => $this->configuration['button_text'],
      ],
    ];
  }

}
