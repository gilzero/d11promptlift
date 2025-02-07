<?php

namespace Drupal\ui_patterns\Plugin\Derivative;

use Drupal\Component\Plugin\PluginBase;

/**
 * Provides derivable context for every field referencing an entity.
 */
class EntityReferenceFieldPropertyDerivableContextDeriver extends EntityFieldSourceDeriverBase {

  /**
   * {@inheritdoc}
   */
  protected function getDerivativeDefinitionsForEntityStorageFieldProperty(string $entity_type_id, string $field_name, string $property, array $base_plugin_derivative): void {
    $id = implode(PluginBase::DERIVATIVE_SEPARATOR, [
      $entity_type_id,
      $field_name,
      $property,
    ]);
    if (!$this->entityFieldsMetadata[$entity_type_id]["field_storages"][$field_name]["properties"][$property]['entity_reference']) {
      return;
    }
    // $base_plugin_derivative["context_definitions"]["field_name"] =
    $this->derivatives[$id] = array_merge(
        $base_plugin_derivative,
        [
          "id" => $id,
          "label" => $this->t("Field prop: entity"),
          "context_requirements" => array_merge($base_plugin_derivative["context_requirements"], ["field_granularity:item"]),
          "tags" => array_merge($base_plugin_derivative["tags"], ["xxx"]),
        ]);
  }

}
