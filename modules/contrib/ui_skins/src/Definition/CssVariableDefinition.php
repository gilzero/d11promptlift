<?php

declare(strict_types=1);

namespace Drupal\ui_skins\Definition;

use Drupal\Component\Plugin\Definition\PluginDefinition;

/**
 * Css variable definition class.
 */
class CssVariableDefinition extends PluginDefinition {

  /**
   * CSS variable definition.
   *
   * @var array
   */
  protected array $definition = [
    'id' => '',
    'enabled' => TRUE,
    'type' => 'textfield',
    'label' => '',
    'description' => '',
    'category' => '',
    'default_values' => [],
    'weight' => 0,
    'additional' => [],
    'provider' => '',
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
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|string
   *   Property value.
   */
  public function getCategory() {
    return $this->definition['category'];
  }

  /**
   * Setter.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|string $category
   *   Property value.
   *
   * @return $this
   */
  public function setCategory($category) {
    $this->definition['category'] = $category;
    return $this;
  }

  /**
   * If the plugin is in a category.
   *
   * @return bool
   *   TRUE if a category is defined.
   */
  public function hasCategory(): bool {
    return !empty($this->getCategory());
  }

  /**
   * Getter.
   *
   * @return array
   *   Property value.
   */
  public function getDefaultValues(): array {
    return $this->definition['default_values'];
  }

  /**
   * Setter.
   *
   * @param array $defaultValues
   *   Property value.
   *
   * @return $this
   */
  public function setDefaultValues(array $defaultValues) {
    $this->definition['default_values'] = $defaultValues;
    return $this;
  }

  /**
   * Check if a value match a scope default value.
   *
   * @param string $scope
   *   The scope.
   * @param string $value
   *   The value to check against.
   *
   * @return bool
   *   TRUE if the value matches the scope default value.
   */
  public function isDefaultScopeValue(string $scope, string $value): bool {
    $default_values = $this->getDefaultValues();

    if (!isset($default_values[$scope])) {
      return FALSE;
    }

    if ($default_values[$scope] == $value) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Getter.
   *
   * @return bool
   *   Property value.
   */
  public function isEnabled(): bool {
    return $this->definition['enabled'];
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getType(): string {
    return $this->definition['type'];
  }

  /**
   * Setter.
   *
   * @param string $type
   *   Property value.
   *
   * @return $this
   */
  public function setType(string $type) {
    $this->definition['type'] = $type;
    return $this;
  }

  /**
   * Getter.
   *
   * @return int
   *   Property value.
   */
  public function getWeight(): int {
    return $this->definition['weight'];
  }

  /**
   * Setter.
   *
   * @param int $weight
   *   Property value.
   *
   * @return $this
   */
  public function setWeight(int $weight) {
    $this->definition['weight'] = $weight;
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
   * Return array definition.
   *
   * @return array
   *   Array definition.
   */
  public function toArray(): array {
    return $this->definition;
  }

}
