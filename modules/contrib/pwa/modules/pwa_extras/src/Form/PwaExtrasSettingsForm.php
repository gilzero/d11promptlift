<?php

namespace Drupal\pwa_extras\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * PWA Extras admin settings form.
 */
class PwaExtrasSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pwa_extras_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('pwa_extras.settings.apple');

    $form['apple'] = [
      '#type' => 'details',
      '#title' => $this->t('Apple/IOS Specific Settings'),
      '#open' => TRUE,
    ];

    $mask_color = $config->get('mask_color') ?: '#0678be';

    $form['apple']['touch_icons'] = [
      "#type" => 'checkboxes',
      "#title" => $this->t('Touch Icons'),
      // @todo Find a way to properly render these icons on the frontend:
      "#options" => pwa_extras_apple_touch_icons(),
      '#default_value' => $config->get('touch_icons'),
    ];

    $form['apple']['mask_color'] = [
      "#type" => 'color',
      "#title" => $this->t('Mask icon color'),
      "#description" => $this->t('The color scheme of OS pinned tab mask icon.'),
      '#default_value' => $mask_color,
      '#states' => [
        'invisible' => [
          ':input[value="touch-icon-mask"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $site_name = $this->config('pwa.config')->get('name') ?: $this->config('system.site')->get('name');
    $color_select = $config->get('color_select') ?: 'default';

    $form['apple']['meta_tags'] = [
      "#type" => 'checkboxes',
      "#title" => $this->t('Meta Tags'),
      // @todo Find a way to properly render these icons on the frontend:
      "#options" => pwa_extras_apple_meta_tags($site_name, $color_select),
      '#default_value' => $config->get('meta_tags'),
    ];

    $form['apple']['color_select'] = [
      "#type" => 'select',
      "#title" => $this->t('Select color for status-bar-style'),
      '#options' => [
        'default' => $this->t('Default'),
        'black' => $this->t('Black'),
        'black_translucent' => $this->t('Black-translucent'),
      ],
      '#states' => [
        'invisible' => [
          ':input[value="meta-status-bar-style"]' => ['checked' => FALSE],
        ],
      ],
      '#default_value' => $color_select,
    ];

    $form['apple']['home_screen_icons'] = [
      "#type" => 'checkboxes',
      "#title" => $this->t('Add to homescreen icons'),
      // @todo Everything inside "#options" is escaped via Html::escape(), hence
      // these links are not rendered. Find a better way to render them:
      "#options" => pwa_extras_apple_home_screen_icons(),
      '#default_value' => $config->get('home_screen_icons'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('pwa_extras.settings.apple');

    $values = $form_state->getValues();
    $config
      ->set('touch_icons', $values['touch_icons'])
      ->set('mask_color', $values['mask_color'])
      ->set('meta_tags', $values['meta_tags'])
      ->set('color_select', $values['color_select'])
      ->set('home_screen_icons', $values['home_screen_icons'])
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['pwa_extras.settings.apple'];
  }

}
