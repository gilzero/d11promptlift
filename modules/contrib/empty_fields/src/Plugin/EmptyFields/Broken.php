<?php

namespace Drupal\empty_fields\Plugin\EmptyFields;

use Drupal\empty_fields\EmptyFieldPluginBase;

/**
 * Defines a fallback plugin for missing empty field plugins.
 *
 * @EmptyField(
 *   id = "broken",
 *   label = @Translation("Broken/Missing"),
 * )
 */
class Broken extends EmptyFieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function react(array $context) {
    return ['#markup' => $this->settingsSummary()];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    return $this->t('This plugin broken or missing.');
  }

}
