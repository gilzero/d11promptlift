<?php

namespace Drupal\empty_fields\Plugin\EmptyFields;

use Drupal\empty_fields\EmptyFieldPluginBase;

/**
 * Defines non-breaking space field.
 *
 * @EmptyField(
 *   id = "nbsp",
 *   label = @Translation("Non-breaking space")
 * )
 */
class EmptyFieldNbsp extends EmptyFieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function react(array $context) {
    return ['#markup' => '&nbsp;'];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    return $this->t('Non-breaking space displayed');
  }

}
