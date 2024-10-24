<?php

namespace Drupal\ai_provider_deepl\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure DeepL API access.
 */
class DeepLConfigForm extends ConfigFormBase {

  /**
   * Config settings.
   */
  const CONFIG_NAME = 'ai_provider_deepl.settings';

  /**
   * Language variants.
   *
   * @see https://developers.deepl.com/docs/resources/supported-languages
   */
  const LANGUAGE_VARIANTS = [
    'en' => [
      'en-gb' => 'English (British)',
      'en-us' => 'English (American)',
    ],
    'pt' => [
      'pt-br' => 'Portuguese (Brazilian)',
      'pt-pt' => 'Portuguese (all Portuguese variants excluding Brazilian Portuguese)',
    ],
    'zh' => [
      'zh-hans' => 'Chinese (simplified)',
      'zh-hant' => 'Chinese (traditional)',
    ],
  ];

  /**
   * The AI Provider service.
   *
   * @var \Drupal\ai\AiProviderPluginManager
   */
  protected $aiProviderManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->aiProviderManager = $container->get('ai.provider');
    $instance->languageManager = $container->get('language_manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'deepl_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::CONFIG_NAME,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::CONFIG_NAME);

    $form['api_key'] = [
      '#type' => 'key_select',
      '#title' => $this->t('DeepL API Key'),
      '#description' => $this->t('The API key of your DeepL subscription.'),
      '#default_value' => $config->get('api_key'),
      '#required' => TRUE,
    ];

    $form['language_variants'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Language Variants'),
      '#description' => $this->t('Specify language variants for translations, see <a href="@url">documentation</a>.', ['@url' => 'https://developers.deepl.com/docs/resources/supported-languages']),
      '#tree' => TRUE,
    ];

    foreach (self::LANGUAGE_VARIANTS as $language_code => $variants) {
      $default = $config->get('language_variants.' . $language_code) ?? reset($variants);
      $form['language_variants'][$language_code] = [
        '#type' => 'select',
        '#title' => $this->t('Language variant for @language_code', ['@language_code' => $language_code]),
        '#options' => $variants,
        '#default_value' => $default,
        '#empty_option' => $this->t('- Select -'),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $api_key = $form_state->getValue('api_key');
    if (empty($api_key)) {
      $form_state->setErrorByName('api_key', $this->t('API key is required.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config(static::CONFIG_NAME);
    $config->set('api_key', $form_state->getValue('api_key'));
    $selected_variants = $form_state->getValue('language_variants');
    foreach ($selected_variants as $language_code => $selected_variant) {
      $config->set('language_variants.' . $language_code, $selected_variant);
    }
    $config->save();

    $this->aiProviderManager->defaultIfNone('translate_text', 'deepl', 'default');

    parent::submitForm($form, $form_state);
  }

}
