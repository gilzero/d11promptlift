<?php

namespace Drupal\publication_date\Plugin\Field\FieldType;

use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Field\ChangedFieldItemList;

/**
 * Defines a item list class for publication date fields.
 */
class PublicationDateFieldItemList extends ChangedFieldItemList {

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    parent::preSave();
    if ($this->isEmpty()) {
      $entity = $this->getEntity();
      if ($entity instanceof EntityPublishedInterface && $entity->isPublished()) {
        $this->set(0, ['value' => \Drupal::time()->getRequestTime()]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __get($property_name) {
    if ($property_name === 'published_at_or_now' && $this->isEmpty()) {
      $this->set(0, ['value' => NULL]);
    }
    return parent::__get($property_name);
  }

}
