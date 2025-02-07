<?php

declare(strict_types=1);

namespace Drupal\ui_skins\Definition;

use Drupal\Component\Plugin\Definition\PluginDefinition;

/**
 * Theme definition class.
 */
class ThemeDefinition extends PluginDefinition {

  /**
   * Theme definition.
   *
   * @var array
   */
  protected array $definition = [
    'id' => '',
    'label' => '',
    'description' => '',
    'target' => 'body',
    'key' => 'class',
    'value' => '',
    'library' => '',
    'additional' => [],
    'provider' => '',
    'dependencies' => [],
  ];

  /**
   * Constructor.
   */
  public function __construct(array $definition = []) {
    foreach ($definition as $name => $value) {
      if (\array_key_exists($name, $this->definition)) {
        $this->definition[$name] = $value;
      }
      else {
        $this->definition['additional'][$name] = $value;
      }
    }

    $this->id = $this->definition['id'];
  }

  /**
   * Getter.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|string
   *   Property value.
   */
  public function getLabel() {
    return $this->definition['label'];
  }

  /**
   * Setter.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|string $label
   *   Property value.
   *
   * @return $this
   */
  public function setLabel($label) {
    $this->definition['label'] = $label;
    return $this;
  }

  /**
   * Getter.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|string
   *   Property value.
   */
  public function getDescription() {
    return $this->definition['description'];
  }

  /**
   * Setter.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|string $description
   *   Property value.
   *
   * @return $this
   */
  public function setDescription($description) {
    $this->definition['description'] = $description;
    return $this;
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getTarget(): string {
    return $this->definition['target'];
  }

  /**
   * Setter.
   *
   * @param string $target
   *   Property value.
   *
   * @return $this
   */
  public function setTarget(string $target) {
    $this->definition['target'] = $target;
    return $this;
  }

  /**
   * Return the target attributes.
   *
   * @return string
   *   The computed target.
   */
  public function getComputedTarget(): string {
    return match ($this->getTarget()) {
      'html' => 'html_attributes',
      default => 'attributes',
    };
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getKey(): string {
    return $this->definition['key'];
  }

  /**
   * Setter.
   *
   * @param string $key
   *   Property value.
   *
   * @return $this
   */
  public function setKey(string $key) {
    $this->definition['key'] = $key;
    return $this;
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getValue(): string {
    return !empty($this->definition['value']) ? $this->definition['value'] : $this->id;
  }

  /**
   * Setter.
   *
   * @param string $value
   *   Property value.
   *
   * @return $this
   */
  public function setValue(string $value) {
    $this->definition['value'] = $value;
    return $this;
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getLibrary(): string {
    return $this->definition['library'];
  }

  /**
   * Setter.
   *
   * @param string $library
   *   Property value.
   *
   * @return $this
   */
  public function setLibrary(string $library) {
    $this->definition['library'] = $library;
    return $this;
  }

  /**
   * Getter.
   *
   * @return array
   *   Property value.
   */
  public function getAdditional(): array {
    return $this->definition['additional'];
  }

  /**
   * Setter.
   *
   * @param array $additional
   *   Property value.
   *
   * @return $this
   */
  public function setAdditional(array $additional) {
    $this->definition['additional'] = $additional;
    return $this;
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getProvider(): string {
    return $this->definition['provider'];
  }

  /**
   * Setter.
   *
   * @param string $provider
   *   Property value.
   *
   * @return $this
   */
  public function setProvider(string $provider) {
    $this->definition['provider'] = $provider;
    return $this;
  }

  /**
   * Getter.
   *
   * @return array
   *   Property value.
   */
  public function getDependencies(): array {
    return $this->definition['dependencies'];
  }

  /**
   * Setter.
   *
   * @param array $dependencies
   *   Property value.
   *
   * @return $this
   */
  public function setDependencies(array $dependencies) {
    $this->definition['dependencies'] = $dependencies;
    return $this;
  }

  /**
   * Return array definition.
   *
   * @return array
   *   Array definition.
   */
  public function toArray(): array {
    return $this->definition;
  }

}
