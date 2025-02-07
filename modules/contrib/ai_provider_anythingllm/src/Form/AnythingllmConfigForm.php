<?php

namespace Drupal\ai_provider_anythingllm\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ai\AiProviderPluginManager;
use Drupal\ai\Service\AiProviderFormHelper;
use Drupal\ai_provider_anythingllm\AnythingllmApi;
use Drupal\key\KeyRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure OpenAI API access.
 */
class AnythingllmConfigForm extends ConfigFormBase {

  /**
   * Config settings.
   */
  const CONFIG_NAME = 'ai_provider_anythingllm.settings';

  /**
   * The AI Provider service.
   *
   * @var \Drupal\ai\AiProviderPluginManager
   */
  protected $aiProviderManager;

  /**
   * The form helper.
   *
   * @var \Drupal\ai\Service\AiProviderFormHelper
   */
  protected $formHelper;

  /**
   * The key factory.
   *
   * @var \Drupal\key\KeyRepositoryInterface
   */
  protected $keyRepository;

  /**
   * The API.
   *
   * @var \Drupal\ai_provider_anythingllm\AnythingllmApi
   */
  protected $aiApi;

  /**
   * Constructs a new OpenAIConfigForm object.
   */
  final public function __construct(AiProviderPluginManager $ai_provider_manager, AiProviderFormHelper $form_helper, KeyRepositoryInterface $key_repository, AnythingllmApi $aiApi) {
    $this->aiProviderManager = $ai_provider_manager;
    $this->formHelper = $form_helper;
    $this->keyRepository = $key_repository;
    $this->aiApi = $aiApi;
  }

  /**
   * {@inheritdoc}
   */
  final public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ai.provider'),
      $container->get('ai.form_helper'),
      $container->get('key.repository'),
      $container->get('ai_provider_anythingllm.api'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'anythingllm_settings';
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

    $form['api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Url'),
      '#description' => $this->t('URL and port of AnythingLLM API.'),
      '#required' => TRUE,
      '#default_value' => $config->get('api_url'),
      '#attributes' => [
        'placeholder' => 'http://127.0.0.1:3001',
      ],
    ];

    $form['api_key'] = [
      '#type' => 'key_select',
      '#title' => $this->t('AnythingLLM API Key'),
      '#description' => $this->t('The API Key. Can be found on <em>https://api.example.com/settings/api-keys</em>.'),
      '#default_value' => $config->get('api_key'),
    ];

    $provider = $this->aiProviderManager->createInstance('anythingllm');
    $form['models'] = $this->formHelper->getModelsTable($form, $form_state, $provider);

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate the api key against model listing.
    $api_url = $form_state->getValue('api_url');
    $key = $form_state->getValue('api_key');
    $api_key = $this->keyRepository->getKey($key)->getKeyValue();
    if (!$api_key) {
      $form_state->setErrorByName('api_key', $this->t('The API Key is invalid.'));
      return;
    }
    $this->aiApi->setApiKey($api_key);
    $this->aiApi->setApiUrl($api_url);

    try {
      $this->aiApi->getModels();
    }
    catch (\Exception $e) {
      $form_state->setErrorByName('api_key', $this->t('The API Key is not working.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->config(static::CONFIG_NAME)
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('api_url', $form_state->getValue('api_url'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
