<?php

namespace Drupal\publication_date\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\TimestampItem;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'published_at' entity field type.
 *
 * Based on a field of this type, entity types can easily implement the
 * EntityChangedInterface.
 *
 * @FieldType(
 *   id = "published_at",
 *   label = @Translation("Publication date"),
 *   description = @Translation("An entity field containing a UNIX timestamp of when the entity has been last updated."),
 *   no_ui = TRUE,
 *   default_widget = "publication_date_timestamp",
 *   default_formatter = "timestamp",
 *   list_class = "\Drupal\publication_date\Plugin\Field\FieldType\PublicationDateFieldItemList"
 * )
 *
 * @see \Drupal\Core\Entity\EntityChangedInterface
 */
class PublicationDateItem extends TimestampItem {

  /**
   * {@inheritdoc}
   */
  public function applyDefaultValue($notify = TRUE) {
    $this->setValue(
      [
        'value' => NULL,
        'published_at_or_now' => \Drupal::time()->getRequestTime(),
      ], $notify
    );
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    $properties['published_at_or_now'] = DataDefinition::create('timestamp')
      ->setLabel(t('Published at or now'))
      ->setComputed(TRUE)
      ->setClass('\Drupal\publication_date\PublishedAtOrNowComputed')
      ->setSetting('source', 'value');

    return $properties;
  }

}
