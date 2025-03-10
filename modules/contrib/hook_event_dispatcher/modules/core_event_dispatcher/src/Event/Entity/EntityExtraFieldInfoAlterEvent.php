<?php

namespace Drupal\core_event_dispatcher\Event\Entity;

use Drupal\Component\EventDispatcher\Event;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\hook_event_dispatcher\Attribute\HookEvent;
use Drupal\hook_event_dispatcher\Event\EventInterface;

/**
 * Class EntityExtraFieldInfoAlterEvent.
 */
#[HookEvent(id: 'entity_extra_field_info_alter', alter: 'entity_extra_field_info')]
class EntityExtraFieldInfoAlterEvent extends Event implements EventInterface {

  /**
   * Field info.
   *
   * @var array
   */
  private array $fieldInfo = [];

  /**
   * EntityExtraFieldInfoAlterEvent constructor.
   *
   * @param array $fieldInfo
   *   Extra field info.
   */
  public function __construct(array &$fieldInfo) {
    $this->fieldInfo = &$fieldInfo;
  }

  /**
   * Get the dispatcher type.
   *
   * @return string
   *   The dispatcher type.
   */
  public function getDispatcherType(): string {
    return EntityHookEvents::ENTITY_EXTRA_FIELD_INFO_ALTER;
  }

  /**
   * Get the field info.
   *
   * @return array
   *   Extra field info.
   */
  public function &getFieldInfo(): array {
    return $this->fieldInfo;
  }

}
