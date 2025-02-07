<?php

namespace Drupal\metatag_ai\Plugin;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\State\State;

/**
 * Main class that generates the metatag using OpenAI.
 */
class GenerateMetatag {
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

    $service = $this->config->get(self::MODULE_PREFIX . '.service');
    if ($service == 'awsbedrock') {
      $api = \Drupal::service('metatag_ai.awsbedrock');
    }
    else {
      $api = \Drupal::service('metatag_ai.openai');
    }

    return $api->generate($text);
  }

  /**
   * Check errors.
   *
   * @return bool
   *   True if an error was logged.
   */
  public function hasErrors() {
    return $this->errors ? TRUE : FALSE;
  }

  /**
   * Return all errors logged.
   *
   * @return array
   *   Array of strings describing the logged errors.
   */
  public function getErrors() {
    return $this->errors;
  }

  /**
   * Log error.
   *
   * @param string $readable_error
   *   This is the error text that will be presented to users.
   * @param string $complete_error
   *   This error text contains additional information for debugging purposes.
   */
  private function logError(string $readable_error, string $complete_error = NULL) {
    if (!$readable_error) {
      return;
    }

    $this->errors[] = $readable_error;
    if ($complete_error) {
      $this->loggerFactory->get(self::MODULE_PREFIX)->error($complete_error);
    }
    else {
      $this->loggerFactory->get(self::MODULE_PREFIX)->error($readable_error);
    }
  }

  /**
   * Save the log to Drupal's logger.
   */
  private function logMessage($message) {
    if ($message) {
      $this->loggerFactory->get(self::MODULE_PREFIX)->notice($message);
    }
  }

  /**
   * Returns boolean.
   */
  private function isJson($string) {
    return is_string($string) && is_array(json_decode($string, TRUE)) && (json_last_error() == JSON_ERROR_NONE) ? TRUE : FALSE;
  }

}
