<?php

namespace Drupal\metatag_ai\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Stores metadata settings.
 */
class MetatagAIConfigForm extends ConfigFormBase {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'metatag_ai.settings',
    ];
  }

  /**
   * Constructs a MetatagAIConfigForm object.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'metatag_ai_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('metatag_ai.settings');
    $state_config = $this->state;

    $form = [];

    $form['service'] = [
      '#required' => TRUE,
      '#type' => 'select',
      '#title' => $this->t('Service'),
      '#options' => [
        'openai' => 'OpenAI',
        'awsbedrock' => 'AWS Bedrock',
      ],
      '#default_value' => $config->get('metatag_ai.service'),
    ];

    $form['openai'] = [
      '#type' => 'details',
      '#title' => $this->t('OpenAI Settings'),
      '#open' => $config->get('metatag_ai.service') == 'openai' ? TRUE : FALSE,
    ];

    $form['openai']['metatagai_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Endpoint'),
      '#description' => $this->t('Please provide the OpenAI API Endpoint here.'),
      '#default_value' => $config->get('metatag_ai.metatagai_endpoint') ?: 'https://api.openai.com/v1/chat/completions',
    ];

    $form['openai']['metatagai_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OpenAI Access Token'),
      '#description' => $this->t('Please provide the OpenAI Access Token here.'),
      '#default_value' => $config->get('metatag_ai.metatagai_token'),
    ];

    $form['openai']['metatagai_model'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OpenAI API Model Name'),
      '#description' => $this->t('Please provide the OpenAI API Model name here.'),
      '#default_value' => $config->get('metatag_ai.metatagai_model') ?: 'gpt-3.5-turbo',
    ];

    $form['openai']['metatagai_max_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OpenAI API Max Token'),
      '#description' => $this->t('Please provide the Max token here to limit the output words. Max token is the limit of tokens combining both input prompt and output text.'),
      '#default_value' => $config->get('metatag_ai.metatagai_max_token') ?: 256,
    ];

    $form['openai']['metatagai_max_context_length'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OpenAI Max Context Length'),
      '#description' => $this->t('Please provide the Maximum context length to limit the input prompt.'),
      '#default_value' => $config->get('metatag_ai.metatagai_max_context_length') ?: 4096,
    ];

    $form['openai']['metatagai_temperature'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OpenAI Temperature'),
      '#description' => $this->t('Please provide the OpenAI Temperature value here.'),
      '#default_value' => $config->get('metatag_ai.metatagai_temperature') ?: 1,
    ];

    $form['awsbedrock'] = [
      '#type' => 'details',
      '#title' => $this->t('AWS Bedrock Settings'),
      '#open' => $config->get('metatag_ai.service') == 'awsbedrock' ? TRUE : FALSE,
    ];

    $form['awsbedrock']['awsbedrock_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client Key'),
      '#default_value' => $config->get('metatag_ai.awsbedrock_api_key'),
    ];

    $form['awsbedrock']['awsbedrock_api_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client Secret'),
      '#default_value' => $config->get('metatag_ai.awsbedrock_api_secret'),
    ];

    $form['awsbedrock']['awsbedrock_region'] = [
      '#type' => 'textfield',
      '#title' => $this->t('AWS Region'),
      '#default_value' => $config->get('metatag_ai.awsbedrock_region'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $access_token = $form_state->getValue('metatagai_token');
    $api_max_token = $form_state->getValue('metatagai_max_token');
    $metatagai_temperature = $form_state->getValue('metatagai_temperature');
    $context_length = $form_state->getValue('metatagai_max_context_length');

    if (!empty($access_token) && !preg_match('/^[A-Za-z0-9-_]+$/', $access_token)) {
      $form_state->setErrorByName('metatagai_token', $this->t('Access Token contains invalid characters. Only alphanumeric characters, hyphens, and underscores are allowed.'));
    }

    if (!empty($api_max_token) && !is_numeric($api_max_token)) {
      $form_state->setErrorByName('metatagai_max_token', $this->t('OpenAI API Max Token must be a number.'));
    }

    if (!empty($metatagai_temperature) && !is_numeric($metatagai_temperature)) {
      $form_state->setErrorByName('metatagai_temperature', $this->t('OpenAI Temperature must be a number.'));
    }

    if (!empty($context_length) && !is_numeric($context_length)) {
      $form_state->setErrorByName('metatagai_max_context_length', $this->t('OpenAI Max Context Length must be a number.'));
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $state_config = $this->state;
    $values = $form_state->getValues();

    $this->config('metatag_ai.settings')
      ->set('metatag_ai.service', $values['service'])
      ->set('metatag_ai.metatagai_endpoint', $values['metatagai_endpoint'])
      ->set('metatag_ai.metatagai_model', $values['metatagai_model'])
      ->set('metatag_ai.metatagai_max_token', $values['metatagai_max_token'])
      ->set('metatag_ai.metatagai_temperature', $values['metatagai_temperature'])
      ->set('metatag_ai.metatagai_token', $values['metatagai_token'])
      ->set('metatag_ai.metatagai_max_context_length', $values['metatagai_max_context_length'])
      ->set('metatag_ai.awsbedrock_api_key', $values['awsbedrock_api_key'])
      ->set('metatag_ai.awsbedrock_api_secret', $values['awsbedrock_api_secret'])
      ->set('metatag_ai.awsbedrock_region', $values['awsbedrock_region'])
      ->save();

  }

}
