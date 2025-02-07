<?php

namespace Drupal\metatag_ai\Plugin;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\State\State;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Main class that generates the metatag using OpenAI.
 */
class Openai {
  const MODULE_PREFIX = 'metatag_ai';

  use TraitLogger;

  /**
   * Stores error messages.
   *
   * @var array
   */
  private $errors = [];

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
   * Call OpenAI api to generate the text.
   */
  public function generate(string $text) {
    $response_body = $this->invokeApi($text);
    if (!$response_body) {
      $this->logError('AI generator returned an empty response.');
      return $response;
    }

    // Extract title and description.
    $response_text = json_decode($response_body->choices[0]->message->content);
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
      $keywords = $response_text->keywords;
    }

    return [
      'title' => $title,
      'description' => $description,
      'abstract' => $abstract,
      'keywords' => $keywords,
    ];
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
  public function invokeApi(string $text) {
    $api_key = $this->config->get(self::MODULE_PREFIX . '.metatagai_token');

    $endpoint = $this->config->get(self::MODULE_PREFIX . '.metatagai_endpoint');
    $max_tokens = (int) $this->config->get(self::MODULE_PREFIX . '.metatagai_max_token');
    $max_context_length = (int) $this->config->get(self::MODULE_PREFIX . '.metatagai_max_context_length');
    $model = $this->config->get(self::MODULE_PREFIX . '.metatagai_model');
    $temperature = (float) $this->config->get(self::MODULE_PREFIX . '.metatagai_temperature');

    if (!$endpoint || !$api_key || !$temperature || !$max_tokens || !$model) {
      if (!$endpoint) {
        $this->logError('Incomplete Open AI credentials: Missing endpoint.');
      }
      if (!$api_key) {
        $this->logError('Incomplete Open AI credentials: Missing API Key.');
      }
      if (!$temperature) {
        $this->logError('Incomplete Open AI credentials: Missing temperature.');
      }
      if (!$max_tokens) {
        $this->logError('Incomplete Open AI credentials: Missing max token.');
      }
      if (!$model) {
        $this->logError('Incomplete Open AI credentials: Missing model.');
      }
      return FALSE;
    }

    $text = substr($text, 0, $max_context_length);

    $message[] = [
      'role' => 'system',
      'content' => 'When i ask for help, you will reply the following information:
        1. title with maximum of 60 characters 
        2. description with maximimum of 160 characters 
        3. abstract - a brief and concise summary of the content with maximum of 160 characters
        4. maximum of 10 keywords
        Suggest content for SEO ranking. Reply in JSON format of the title, description, abstract and keywords',
    ];
    $message[] = [
      "role" => "user",
      "content" => $text,
    ];

    $body = [
      'model' => $model,
      'messages' => $message,
      'temperature' => $temperature,
      'max_tokens' => $max_tokens,
      "top_p" => 1,
      "frequency_penalty" => 0,
      "presence_penalty" => 0,
    ];

    $data = [
      'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $api_key,
      ],
      'body' => json_encode($body),
    ];

    try {
      $client = new Client();
      $response = $client->post($endpoint, $data);
      $contents = $response->getBody()->getContents();

      if (!$this->isJson($contents)) {
        $this->logError('AI generator returned an invalid format', 'Openapi returned an invalid format' . $contents);
      }
      else {
        $this->logMessage($contents);
      }

      return json_decode($contents);
    }
    catch (GuzzleException $exception) {
      $response = $exception->getResponse();
      $response_body = $response->getBody()->getContents();
      if ($this->isJson($response_body)) {
        $error = json_decode($response_body);
        $this->logError($error->error->message);
      }
      else {
        $this->logError((string) $response_body);
      }
    }

    return FALSE;
  }

}
