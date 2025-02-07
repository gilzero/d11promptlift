<?php

namespace Drupal\publication_date;

use Drupal\Core\TypedData\TypedData;

/**
 * Published at or now data time.
 */
class PublishedAtOrNowComputed extends TypedData {

  /**
   * The data value.
   *
   * @var mixed
   */
  protected $value;

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    /** @var \Drupal\Core\Field\FieldItemInterface $item */
    $item = $this->getParent();
    $value = $item->get($this->definition->getSetting('source'))->getValue();
    if ($value) {
      return $value;
    }
    return \Drupal::time()->getRequestTime();
  }

}
