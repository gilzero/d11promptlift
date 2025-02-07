<?php

namespace Drupal\ai_provider_anythingllm;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\key\KeyRepositoryInterface;
use GuzzleHttp\Client;

/**
 * Basic AnythingLLM API.
 */
class AnythingllmApi {

  /**
   * The GuzzleHttp client.
   */
  protected Client $client;

  /**
   * The key factory.
   *
   * @var \Drupal\key\KeyRepositoryInterface
   */
  protected $keyRepository;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * API Key.
   */
  private ?string $apiKey;

  /**
   * API Url.
   */
  private ?string $apiUrl;

  /**
   * API Prefix.
   */
  private string $apiPrefix = '/api/v1/';

  /**
   * Constructs a new AnythingllmApi object.
   *
   * @param \GuzzleHttp\Client $client
   *   Http client.
   * @param \Drupal\key\KeyRepositoryInterface $key_repository
   *   Repository from key module.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The Drupal config factory.
   */
  public function __construct(Client $client, KeyRepositoryInterface $key_repository, ConfigFactoryInterface $configFactory) {
    $this->client = $client;
    $this->keyRepository = $key_repository;
    $this->configFactory = $configFactory;
    $this->config = $this->configFactory->get('ai_provider_anythingllm.settings');

    $this->setConnection();
  }

  /**
   * Sets API key and url for connection.
   */
  private function setConnection() {
    $api_url = $this->config->get('api_url');
    $this->setApiUrl($api_url);

    $key = $this->config->get('api_key');
    if ($key) {
      $api_key = $this->keyRepository->getKey($key)->getKeyValue();
      $this->setApiKey($api_key);
    }
  }

  /**
   * Set the API Key.
   *
   * @param string $api_key
   *   The API Key.
   */
  public function setApiKey($api_key) {
    $this->apiKey = $api_key;
  }

  /**
   * Set the API Url.
   *
   * @param string $api_url
   *   The API Url.
   */
  public function setApiUrl($api_url) {
    $this->apiUrl = $api_url;
  }

  /**
   * Checks if the api is set.
   *
   * @return bool
   *   If the api is set.
   */
  public function isApiSet() {
    return !empty($this->apiKey) && !empty($this->apiUrl);
  }

  /**
   * Makes a chat call.
   *
   * @param array $payload
   *   Payload for the chat call.
   * @param array $model_config
   *   Settings for this model.
   */
  public function chat($payload, $model_config = []) {
    if (isset($model_config['chat_endpoint']) && $model_config['chat_endpoint'] == 'anythingllm') {
      // Use AnythingLLM API.
      return $this->chatAnythingllm($payload);
    }
    else {
      // Use OpenAI compatible chat API.
      return $this->chatOpenai($payload);
    }
  }

  /**
   * Internal function for AnythingLLM chat call.
   *
   * @param array $payload
   *   Payload for the chat call.
   */
  protected function chatAnythingllm($payload) {

    $message = end($payload['messages']);
    $content = $message['content'];
    $data = [
      'message' => $content,
      'mode' => 'chat',
      'attachments' => $message['images'],
      'sessionId' => $payload['session_id'],
    ];

    $url = 'workspace/' . $payload['model'] . '/chat';
    $result = json_decode($this->call($url, 'POST', $data), TRUE);

    $return = [
      'id' => $result['id'],
      'object' => 'chat.completion',
      'created' => time(),
      'model' => $payload['model'],
      'choices' => [
        [
          'message' => [
            'role' => 'assistant',
            'content' => $result['textResponse'],
          ],
          'logprobs' => NULL,
          'finish_reason' => 'stop',
        ],
      ],
    ];

    return $return;
  }

  /**
   * Internal function for OpenAI compatible chat call.
   *
   * @param array $payload
   *   Payload for the chat call.
   */
  protected function chatOpenai($payload) {
    $result = $this->call('openai/chat/completions', 'POST', $payload);
    $return = json_decode($result, TRUE);

    return $return;
  }

  /**
   * Makes a embeddings call.
   *
   * @param array $payload
   *   Payload for the chat call.
   */
  public function embeddings($payload) {
    $result = $this->call('openai/embeddings', 'POST', $payload);
    $return = json_decode($result, TRUE);

    return $return;
  }

  /**
   * Store Html document.
   *
   * @param array $payload
   *   Payload for the chat call.
   */
  public function createFolder($payload) {
    try {
      $result = $this->call('document/create-folder', 'POST', $payload);
      $return = json_decode($result, TRUE);
      return $return;
    }
    catch (\Exception $exception) {
    }
  }

  /**
   * Move file.
   *
   * @param array $payload
   *   Payload for the chat call.
   */
  public function moveFile($payload) {
    $result = $this->call('document/move-files', 'POST', $payload);
    $return = json_decode($result, TRUE);

    return $return;
  }

  /**
   * Remove documents.
   *
   * @param array $payload
   *   Payload for the chat call.
   */
  public function removeDocuments($payload) {
    $result = $this->call('system/remove-documents', 'DELETE', $payload);
    $return = json_decode($result, TRUE);

    return $return;
  }

  /**
   * Store Html document.
   *
   * @param array $payload
   *   Payload for the chat call.
   */
  public function rawText($payload) {
    $result = $this->call('document/raw-text', 'POST', $payload);
    $return = json_decode($result, TRUE);

    return $return;
  }

  /**
   * Get documents.
   */
  public function getDocuments() {
    $result = $this->call('documents', 'GET');
    $return = json_decode($result, TRUE);

    return $return;
  }

  /**
   * Update Embeddings.
   *
   * @param array $payload
   *   Payload for the call.
   */
  public function updateEmbeddings($payload) {
    $url = 'workspace/' . $payload['model_id'] . '/update-embeddings';
    $result = $this->call($url, 'POST', $payload['data']);
    $return = json_decode($result, TRUE);

    return $return;
  }

  /**
   * Get workspaces from AnythingLLM.
   */
  public function getModels() {
    return json_decode($this->call('workspaces'), TRUE);
  }

  /**
   * Make API call.
   *
   * @param string $apiEndPoint
   *   The api endpoint.
   * @param string $method
   *   The http method.
   * @param string $json
   *   JSON params (body).
   * @param string $file
   *   A (real) filepath.
   *
   * @return string|object
   *   The return response.
   */
  protected function call($apiEndPoint, $method = 'GET', $json = NULL, $file = NULL) {
    // Check for API Key and Url.
    if (empty($this->apiKey)) {
      throw new \Exception('No AnythingLLM API Key found.');
    }
    if (empty($this->apiUrl)) {
      throw new \Exception('No AnythingLLM API Url found.');
    }

    // Set options for http client.
    $options = [
      'connect_timeout' => 120,
      'read_timeout' => 300,
      'headers' => [
        'Authorization' => 'Bearer ' . $this->apiKey,
        'accept' => 'application/json',
      ],
    ];

    // Handle json data.
    if ($json) {
      $options['body'] = json_encode($json);
      $options['headers']['Content-Type'] = 'application/json';
    }

    // Handle file.
    if ($file) {
      $options['body'] = fopen($file, 'r');
    }

    // Build URL.
    $url = $this->apiUrl . $this->apiPrefix . $apiEndPoint;

    // Make API call and return body content.
    $res = $this->client->request($method, $url, $options);
    return $res->getBody()->getContents();

  }

}
