<?php

declare(strict_types=1);

namespace Drupal\ui_patterns\Element;

use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Plugin\Component;
use Drupal\Core\Render\Element;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\Theme\ComponentPluginManager;
use Drupal\ui_patterns\ComponentPluginManager as UiPatternsComponentPluginManager;
use Drupal\ui_patterns\PropTypePluginManager;
use Drupal\ui_patterns\SourceInterface;
use Drupal\ui_patterns\SourcePluginBase;
use Drupal\ui_patterns\SourcePluginManager;
use Psr\Log\LoggerInterface;

/**
 * Component render element builder.
 */
class ComponentElementBuilder implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['build'];
  }

  /**
   * Constructs a ComponentElementBuilder.
   */
  public function __construct(
    protected SourcePluginManager $sourcesManager,
    protected PropTypePluginManager $propTypeManager,
    protected ComponentPluginManager $componentPluginManager,
    protected LoggerInterface $logger,
  ) {
  }

  /**
   * Build component data provided to the SDC element.
   */
  public function build(array $element): array {
    if (!isset($element['#ui_patterns']) || !isset($element['#component'])) {
      return $element;
    }
    $configuration = $element['#ui_patterns'];
    $contexts = $element['#source_contexts'] ?? [];
    $component = $this->componentPluginManager->find($element['#component']);
    $element = $this->buildProps($element, $component, $configuration, $contexts);
    $element = $this->buildSlots($element, $component, $configuration, $contexts);
    $element['#propsAlter'] = [];
    $element['#slotsAlter'] = [];
    return $element;
  }

  /**
   * Add props to the renderable.
   */
  protected function buildProps(array $build, Component $component, array $configuration, array $contexts): array {
    $props = $component->metadata->schema['properties'] ?? [];
    foreach ($props as $prop_id => $prop_definition) {
      if ($prop_id === 'variant') {
        $prop_configuration = $configuration['variant_id'] ?? [];
      }
      else {
        $prop_configuration = $configuration['props'][$prop_id] ?? [];
      }
      $build = $this->buildProp($build, $prop_id, $prop_definition, $prop_configuration, $contexts);
    }
    return $build;
  }

  /**
   * Add a single prop to the renderable.
   */
  protected function buildProp(array $build, string $prop_id, array $definition, array $configuration, array $source_contexts): array {
    if (isset($build["#props"][$prop_id])) {
      // Keep existing props. No known use case yet.
      return $build;
    }
    $source = $this->getSource($prop_id, $definition, $configuration, $source_contexts);
    if (!$source) {
      return $build;
    }
    try {
      $build = $source->alterComponent($build);
      $prop_type = $definition['ui_patterns']['type_definition'];
      $data = $source->getValue($prop_type);

      if (empty($data) && $prop_type->getPluginId() !== 'attributes') {
        // For JSON Schema validator, empty value is not the same as missing
        // value, and we want to prevent some of the prop types rules to be
        // applied on empty values: string pattern, string format, enum, number
        // min/max...
        // However, we don't remove empty attributes to avoid an error with
        // Drupal\Core\Template\TwigExtension::createAttribute() when themers
        // forget to use the default({}) filter.
        return $build;
      }
      $build["#props"][$prop_id] = $data;
    }
    catch (ContextException $e) {
      // ContextException is thrown when a required context is missing.
      // We don't want to break the render process, so we just ignore the prop.
      $error_message = t("Context error for prop '@prop_id' in component '@component_id': @message", [
        '@prop_id' => $prop_id,
        '@component_id' => $build['#component'],
        '@message' => $e->getMessage(),
      ]);
      $this->logger->error($error_message);
    }
    return $build;
  }

  /**
   * Get Source plugin for a prop.
   *
   * @param string $prop_or_slot_id
   *   Prop ID or slot ID.
   * @param array $definition
   *   Definition.
   * @param array $configuration
   *   Configuration.
   * @param array $source_contexts
   *   Source contexts.
   *
   * @return \Drupal\ui_patterns\SourceInterface|null
   *   The source found or NULL.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function getSource(string $prop_or_slot_id, array $definition, array $configuration, array $source_contexts) : ?SourceInterface {
    $source_id = $configuration["source_id"] ?? NULL;
    if (!$source_id && isset($definition['ui_patterns']['type_definition'])) {
      $source_id = $this->sourcesManager->getPropTypeDefault($definition['ui_patterns']['type_definition']->getPluginId(), $source_contexts);
    }
    if (!$source_id) {
      return NULL;
    }
    /** @var \Drupal\ui_patterns\SourceInterface $source */
    $source = $this->sourcesManager->createInstance(
      $source_id,
      SourcePluginBase::buildConfiguration($prop_or_slot_id, $definition, $configuration, $source_contexts)
    );
    return $source;
  }

  /**
   * Add slots to the renderable.
   */
  protected function buildSlots(array $build, Component $component, array $configuration, array $contexts): array {
    $slots = $component->metadata->slots ?? [];

    foreach ($slots as $slot_id => $slot_definition) {
      $slot_configuration = $configuration['slots'][$slot_id] ?? [];
      $build = $this->buildSlot($build, $slot_id, $slot_definition, $slot_configuration, $contexts);
    }
    return $build;
  }

  /**
   * Add a single slot to the renderable.
   */
  protected function buildSlot(array $build, string $slot_id, array $definition, array $configuration, array $contexts): array {
    if (isset($build["#slots"][$slot_id])) {
      // Keep existing slots. Used by ComponentLayout for example.
      return $build;
    }
    if (!isset($configuration["sources"])) {
      return $build;
    }
    // Slots can have many sources while props can have only one.
    $build["#slots"][$slot_id] = [];
    /** @var \Drupal\ui_patterns\PropTypeInterface $slot_prop_type */
    $slot_prop_type = $this->propTypeManager->createInstance("slot", []);
    // Add sources data to the slot.
    foreach ($configuration["sources"] as $source_configuration) {
      $source = $this->getSource($slot_id, $definition, $source_configuration, $contexts);
      if (!$source) {
        continue;
      }
      $build = $source->alterComponent($build);
      $source_value = $source->getValue($slot_prop_type);
      if (Element::isRenderArray($source_value)) {
        $build["#slots"][$slot_id][] = $this->isSingletonRenderArray($source_value) ? array_values($source_value)[0] : $source_value;
      }
    }
    if ($this->isSingletonRenderArray($build["#slots"][$slot_id])) {
      $build["#slots"][$slot_id] = $build["#slots"][$slot_id][0];
    }

    return $build;
  }

  /**
   * Check if the render array is a singleton.
   *
   * @param array $candidate
   *   The render array to check.
   *
   * @return bool
   *   TRUE if the render array is a singleton, FALSE otherwise.
   */
  protected function isSingletonRenderArray(array $candidate): bool {
    if (count($candidate) !== 1) {
      return FALSE;
    }
    $key = array_key_first($candidate);
    return (is_int($key) || ($key === '') || $key[0] !== '#');
  }

  /**
   * Calculate a component dependencies.
   *
   * @param string|null $component_id
   *   Component ID.
   * @param array $configuration
   *   Component Configuration.
   * @param array $contexts
   *   Contexts.
   *
   * @return array
   *   An array of dependencies keyed by the type of dependency.
   *
   * @see \Drupal\Core\Config\Entity\ConfigDependencyManager
   *
   * @throws \Drupal\Core\Render\Component\Exception\ComponentNotFoundException
   */
  public function calculateComponentDependencies(?string $component_id = NULL, array $configuration = [], array $contexts = []) : array {
    $dependencies = [];
    try {
      $component = $this->componentPluginManager->find($component_id ?? $configuration['component_id']);
      if ($this->componentPluginManager instanceof UiPatternsComponentPluginManager) {
        SourcePluginBase::mergeConfigDependencies($dependencies, $this->componentPluginManager->calculateDependencies($component));
      }
      SourcePluginBase::mergeConfigDependencies($dependencies, $this->calculateComponentDependenciesProps($component, $configuration, $contexts));
      SourcePluginBase::mergeConfigDependencies($dependencies, $this->calculateComponentDependenciesSlots($component, $configuration, $contexts));
    }
    catch (\Throwable $exception) {
      // During install mergeConfigDependencies can lead to
      // unexpected configuration states. We can ignore it.
    }
    return $dependencies;
  }

  /**
   * Calculate a component dependencies for props.
   *
   * @param \Drupal\Core\Plugin\Component $component
   *   Component instance.
   * @param array $configuration
   *   Component Configuration.
   * @param array $contexts
   *   Contexts.
   *
   * @return array
   *   An array of dependencies keyed by the type of dependency.
   */
  protected function calculateComponentDependenciesProps(Component $component, array $configuration = [], array $contexts = []) : array {
    $dependencies = [];
    $props = $component->metadata->schema['properties'] ?? [];
    foreach ($props as $prop_id => $definition) {
      if ($prop_id === 'variant') {
        continue;
      }
      if ($source = $this->getSource($prop_id, $definition, $configuration['props'][$prop_id] ?? [], $contexts)) {
        SourcePluginBase::mergeConfigDependencies($dependencies, $source->calculateDependencies());
      }
    }
    return $dependencies;
  }

  /**
   * Calculate a component dependencies for slots.
   *
   * @param \Drupal\Core\Plugin\Component $component
   *   Component instance.
   * @param array $configuration
   *   Component Configuration.
   * @param array $contexts
   *   Contexts.
   *
   * @return array
   *   An array of dependencies keyed by the type of dependency.
   */
  protected function calculateComponentDependenciesSlots(Component $component, array $configuration = [], array $contexts = []) : array {
    $dependencies = [];
    $slots = $component->metadata->slots ?? [];
    foreach ($slots as $slot_id => $definition) {
      $slot_configuration = $configuration['slots'][$slot_id] ?? [];
      if (!isset($slot_configuration["sources"]) || !is_array($slot_configuration["sources"])) {
        continue;
      }
      foreach ($slot_configuration["sources"] as $source_configuration) {
        if ($source = $this->getSource($slot_id, $definition, $source_configuration, $contexts)) {
          SourcePluginBase::mergeConfigDependencies($dependencies, $source->calculateDependencies());
        }
      }
    }
    return $dependencies;
  }

}
