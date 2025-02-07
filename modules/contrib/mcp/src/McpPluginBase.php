<?php

declare(strict_types=1);

namespace Drupal\mcp;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;

/**
 * Base class for MCP plugins.
 */
abstract class McpPluginBase extends PluginBase implements McpInterface {

  /**
   * {@inheritdoc}
   */
  public function getConfiguration(): array {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration): void {
    $this->configuration = NestedArray::mergeDeep(
      $this->defaultConfiguration(),
      $configuration
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'enabled' => TRUE,
      'config'  => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(
    array $form,
    FormStateInterface $form_state,
  ): array {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(
    array &$form,
    FormStateInterface $form_state,
  ): void {}

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(
    array &$form,
    FormStateInterface $form_state,
  ): void {
    if (!$form_state->getErrors()) {
      $this->configuration['enabled'] = (bool) $form_state->getValue('enabled');
      if ($config = $form_state->getValue('config')) {
        $this->configuration['config'] = $config;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function checkRequirements(): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getTools(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getResources(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getResourceTemplates(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function executeTool(string $toolId, mixed $arguments): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function readResource(string $resourceId): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  final public function isEnabled(): bool {
    return $this->configuration['enabled'] ?? TRUE;
  }

}
