<?php

namespace Drupal\metatag_ai\Plugin;

/**
 * Reusable methods for logging errors.
 */
trait TraitLogger {

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
