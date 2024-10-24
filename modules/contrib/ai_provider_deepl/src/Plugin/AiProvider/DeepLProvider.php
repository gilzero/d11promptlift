<?php

namespace Drupal\ai_provider_deepl\Plugin\AiProvider;

use DeepL\Translator;
use Drupal\ai\Attribute\AiProvider;
use Drupal\ai\Base\AiProviderClientBase;
use Drupal\ai\OperationType\TranslateText\TranslateTextInput;
use Drupal\ai\OperationType\TranslateText\TranslateTextInterface;
use Drupal\ai\OperationType\TranslateText\TranslateTextOutput;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\Yaml\Yaml;

/**
 * Plugin implementation of the 'deepl' provider.
 */
#[AiProvider(
  id: 'deepl',
  label: new TranslatableMarkup('DeepL'),
)]
class DeepLProvider extends AiProviderClientBase implements
  ContainerFactoryPluginInterface,
  TranslateTextInterface {

  /**
   * The OpenAI Client for API calls.
   *
   * @var \OpenAI\Client|null
   */
  protected $client;

  /**
   * API Key.
   *
   * @var string
   */
  protected string $apiKey = '';

  /**
   * {@inheritdoc}
   */
  public function getConfiguredModels(?string $operation_type = NULL, array $capabilities = []): array {
    // For this provider, we only have one model.
    return ['default' => 'Default'];
  }

  /**
   * {@inheritdoc}
   */
  public function isUsable(?string $operation_type = NULL, array $capabilities = []): bool {
    // If it's not configured, it is not usable.
    if (!$this->getConfig()->get('api_key')) {
      return FALSE;
    }
    // If it's one of the bundles that DeepL supports, it's usable.
    if ($operation_type) {
      return in_array($operation_type, $this->getSupportedOperationTypes());
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getSupportedOperationTypes(): array {
    return [
      'translate_text',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(): ImmutableConfig {
    return $this->configFactory->get('ai_provider_deepl.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function getApiDefinition(): array {
    // Load the configuration.
    return Yaml::parseFile($this->moduleHandler->getModule('ai_provider_deepl')->getPath() . '/definitions/api_defaults.yml');
  }

  /**
   * {@inheritdoc}
   */
  public function getModelSettings(string $model_id, array $generalConfig = []): array {
    return $generalConfig;
  }

  /**
   * {@inheritdoc}
   */
  public function setAuthentication(mixed $authentication): void {
    // Set the new API key and reset the client.
    $this->apiKey = $authentication;
    $this->client = NULL;
  }

  /**
   * Gets the raw client.
   *
   * This is the client for inference.
   *
   * @return \DeepL\Translator
   *   The OpenAI client.
   */
  public function getClient(): Translator {
    $this->loadClient();
    return $this->client;
  }

  /**
   * Loads the DeepL Client with authentication if not initialized.
   */
  protected function loadClient(): void {
    if (!$this->client) {
      if (!$this->apiKey) {
        $this->setAuthentication($this->loadApiKey());
      }
      $this->client = new Translator($this->apiKey);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function translateText(TranslateTextInput $input, string $model_id, array $options = []): TranslateTextOutput {
    $text = $input->getText();
    $sourceLanguage = $input->getSourceLanguage();
    $targetLanguage = $input->getTargetLanguage();
    $languageVariants = $this->getConfig()->get('language_variants');
    if (!empty($languageVariants[$targetLanguage])) {
      $targetLanguage = $languageVariants[$targetLanguage];
    }

    try {
      /** @var \DeepL\TextResult $translated */
      $translated = $this->getClient()->translateText($text, $sourceLanguage, $targetLanguage, $options);
    }
    catch (\Exception $e) {
      return new TranslateTextOutput('', $text, $e->getMessage());
    }

    return new TranslateTextOutput($translated->text, $translated, []);

  }

  /**
   * Load API key from key module.
   *
   * @return string
   *   The API key.
   */
  protected function loadApiKey(): string {
    return $this->keyRepository->getKey($this->getConfig()->get('api_key'))->getKeyValue();
  }

}
