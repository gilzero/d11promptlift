<?php

namespace Drupal\metatag_ai\Plugin;

use Aws\Credentials\Credentials;
use Aws\BedrockRuntime\BedrockRuntimeClient;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\State\State;

/**
 * Main class that generates the metatag using AWS Bedrock.
 */
class Bedrock {
  const MODULE_PREFIX = 'metatag_ai';

  use TraitLogger;

  /**
   * Stores error messages.
   *
   * @var array
   */
  private $errors = [];

  /**
   * Bedrock handler.
   *
   * @var \Aws\BedrockRuntime\BedrockRuntimeClient
   */
  private $bedrockRuntimeClient;

  /**
   * Stores Drupal logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private $logger;

  /**
   * Stores Drupal configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructor method.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\State\State $state
   *   The object State.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   Logger factory.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    State $state,
    LoggerChannelFactoryInterface $loggerFactory
  ) {
    $this->loggerFactory = $loggerFactory;
    $this->configFactory = $config_factory;
    $this->state = $state;

    $this->config = $this->configFactory->get(self::MODULE_PREFIX . '.settings');
  }

  /**
   * Accepts and returns the result in array.
   *
   * @param string $text
   *   The text to be processed.
   *
   * @return array
   *   Array with title, description, abstract, and keywords needed by Metatag.
   */
  public function generate(string $text) {
    if (!$text) {
      $this->logError('No title received!');
      return FALSE;
    }

    $region = $this->config->get(self::MODULE_PREFIX . '.awsbedrock_region');
    $api_key = $this->config->get(self::MODULE_PREFIX . '.awsbedrock_api_key');
    $api_secret = $this->config->get(self::MODULE_PREFIX . '.awsbedrock_api_secret');

    $version = 'latest';
    $credentials = new Credentials($api_key, $api_secret);

    $this->bedrockRuntimeClient = new BedrockRuntimeClient([
      'region' => $region,
      'version' => $version,
      'credentials' => $credentials,
    ]);

    $text = strip_tags($text);
    $response_text = $this->invokeModel($text);

    $title = $description = $abstract = $keywords = '';

    if (isset($response_text->title)) {
      $title = $response_text->title;
    }

    if (isset($response_text->description)) {
      $description = $response_text->description;
    }

    if (isset($response_text->abstract)) {
      $abstract = $response_text->abstract;
    }

    if (isset($response_text->keywords)) {
      $keywords = implode(',', $response_text->keywords);
    }

    return [
      'title' => $title,
      'description' => $description,
      'abstract' => $abstract,
      'keywords' => $keywords,
    ];
  }

  /**
   * Invoke the AWS model.
   */
  private function invokeModel($prompt) {
    try {
      $content = [
        "prompt" => "\n\nHuman:When i ask for help, you will reply the following information:"
        . "\n1. title with maximum of 60 characters"
        . "\n2. description with maximimum of 160 characters"
        . "\n3. abstract - a brief and concise summary of the content with maximum of 150 characters"
        . "\n4. maximum of 10 keywords"
        . "\nSuggest content for SEO ranking. Reply in JSON format of the title, description, abstract"
        . " and keywords and nothing else. Here is the text: $prompt."
        . "\n\nAssistant:",
        "max_tokens_to_sample" => 1000,
        "anthropic_version" => "bedrock-2023-05-31",
      ];

      $payload = [
        'contentType' => 'application/json',
        'body' => json_encode($content),
        'modelId' => 'anthropic.claude-v2:1',
      ];

      $response = $this->bedrockRuntimeClient->invokeModel($payload);
      $contents = $response['body']->getContents();

      if (!$this->isJson($contents)) {
        $this->logError('AI generator returned an invalid format', 'AWS Bedrock returned an invalid format' . $contents);
      }
      else {
        $this->logMessage($contents);
      }

      $contents = json_decode($contents);
      $matches = [];
      $completion = preg_match("/\{([^}]*)\}/", $contents->completion, $matches);
      if ($matches && $matches[0]) {
        return json_decode($matches[0]);
      }

      return FALSE;
    }
    catch (Exception $e) {
      $this->logError("Error: ({$e->getCode()}) - {$e->getMessage()}\n");
    }

    return FALSE;
  }

}
