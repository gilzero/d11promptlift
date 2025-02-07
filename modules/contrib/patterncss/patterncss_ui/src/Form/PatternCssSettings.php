<?php

namespace Drupal\patterncss_ui\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures pattern settings.
 */
class PatternCssSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'patterncss_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Attach PatternCSS settings page library.
    $form['#attached']['library'][] = 'patterncss_ui/pattern-settings';

    // Get current settings.
    $config = $this->config('patterncss.settings');

    $form['settings'] = [
      '#type'  => 'details',
      '#title' => $this->t('Pattern settings'),
      '#open'  => TRUE,
    ];

    // Let module handle load Pattern.css library.
    $form['settings']['load'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Load Pattern.css library'),
      '#default_value' => $config->get('load'),
      '#description'   => $this->t("If enabled, this module will attempt to load the Pattern.css library for your site. To prevent loading twice, leave this option disabled if you're including the assets manually or through another module or theme."),
    ];

    // Load method library from CDN or Locally.
    $form['settings']['method'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Add Pattern.css method'),
      '#options'       => [
        'local' => $this->t('Local'),
        'cdn'   => $this->t('CDN'),
      ],
      '#default_value' => $config->get('method'),
      '#description'   => $this->t('These settings control how the Pattern.css library is loaded. You can choose to load from the CDN (External source) or from the local (Internal library).'),
    ];

    // Production or minimized version.
    $form['settings']['build'] = [
      '#type'        => 'fieldset',
      '#title'       => $this->t('Development or Production version'),
      '#collapsible' => TRUE,
      '#collapsed'   => FALSE,
      '#states'      => [
        'invisible' => [
          'select[name="compat"]' => ['checked' => TRUE],
        ],
        'disabled'  => [
          ':input[name="compat"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['settings']['build']['variant'] = [
      '#type'          => 'radios',
      '#title'         => $this->t('Choose minimized or non-minimized library.'),
      '#options'       => [
        0 => $this->t('Use non-minimized library (Development)'),
        1 => $this->t('Use minimized library (Production)'),
      ],
      '#default_value' => $config->get('build.variant'),
      '#description'   => $this->t('These settings work with both local library and CDN methods, but this not applicable in the compat version because the compatible library is only available in minimized mode.'),
    ];

    // Load Pattern.css library Per-path.
    $form['settings']['url'] = [
      '#type'        => 'fieldset',
      '#title'       => $this->t('Load on specific URLs'),
      '#collapsible' => TRUE,
      '#collapsed'   => TRUE,
    ];
    $form['settings']['url']['url_visibility'] = [
      '#type'          => 'radios',
      '#title'         => $this->t('Load pattern.css on specific pages'),
      '#options'       => [
        0 => $this->t('All pages except those listed'),
        1 => $this->t('Only the listed pages'),
      ],
      '#default_value' => $config->get('request_path.negate'),
    ];
    $form['settings']['url']['url_pages'] = [
      '#type'          => 'textarea',
      '#title'         => '<span class="element-invisible">' . $this->t('Pages') . '</span>',
      '#default_value' => _patterncss_ui_array_to_string($config->get('request_path.pages')),
      '#description'   => $this->t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. An example path is %admin-wildcard for every user page. %front is the front page.", [
        '%admin-wildcard' => '/admin/*',
        '%front'          => '<front>',
      ]),
    ];

    // Pattern.css Utilities,
    // Comes packed with a few utility classes to simplify its use.
    $form['options'] = [
      '#type'  => 'details',
      '#title' => $this->t('Pattern default options'),
      '#open'  => TRUE,
    ];

    // List of selectors to individual Pattern Control with Pattern.css.
    $form['options']['selectors'] = [
      '#type'          => 'textarea',
      '#title'         => $this->t('Global selectors'),
      '#default_value' => _patterncss_ui_array_to_string($config->get('options.selector')),
      '#description'   => $this->t('Enter CSS selector (id/class) of your elements e.g., "#id" or ".classname". Use a new line for each selector.'),
      '#rows'          => 3,
    ];

    // The pattern to use.
    $form['options']['pattern'] = [
      '#title'         => $this->t('Pattern'),
      '#type'          => 'select',
      '#options'       => patterncss_patterns(),
      '#default_value' => $config->get('options.pattern'),
      '#description'   => $this->t('Select the pattern name you want to use for CSS selectors globally.'),
    ];

    // Pattern size.
    $form['options']['size'] = [
      '#title'         => $this->t('Size'),
      '#type'          => 'select',
      '#options'       => patterncss_sizes(),
      '#default_value' => $config->get('options.size'),
      '#description'   => $this->t('Each pattern is available in 4 sizes sm, md, lg, and xl'),
      '#states'        => [
        'visible' => [
          ':input[name="infinite"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // @todo Pattern field verification.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Save the updated Pattern.css settings.
    $this->config('patterncss.settings')
      ->set('load', $values['load'])
      ->set('method', $values['method'])
      ->set('build.variant', $values['variant'])
      ->set('request_path.negate', $values['url_visibility'])
      ->set('request_path.pages', _patterncss_ui_string_to_array($values['url_pages']))
      ->set('options.selector', _patterncss_ui_string_to_array($values['selectors']))
      ->set('options.pattern', $values['pattern'])
      ->set('options.size', $values['size'])
      ->save();

    // Flush caches so the updated config can be checked.
    drupal_flush_all_caches();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'patterncss.settings',
    ];
  }

}
