<?php

namespace Drupal\empty_fields;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;

/**
 * Defines a base empty field item implementation.
 *
 * @see \Drupal\empty_fields\Annotation\EmptyField
 * @see \Drupal\empty_fields\EmptyFieldPluginInterface
 * @see \Drupal\empty_fields\EmptyFieldsPluginManager
 * @see plugin_api
 */
abstract class EmptyFieldPluginBase extends PluginBase implements EmptyFieldPluginInterface, ConfigurableInterface {

  /**
   * {@inheritdoc}
   */
  abstract public function react(array $context);

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  abstract public function settingsSummary();

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = NestedArray::mergeDeep(
      $this->defaultConfiguration(),
      $configuration
    );
  }

}
