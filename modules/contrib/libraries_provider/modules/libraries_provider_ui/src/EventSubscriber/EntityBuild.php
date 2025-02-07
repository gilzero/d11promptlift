<?php

namespace Drupal\libraries_provider_ui\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityTypeBuildEvent;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Perform changes on the existing libraries.
 */
class EntityBuild implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_TYPE_BUILD => 'libraryEntityBuild',
    ];
  }

  /**
   * Respond to the event.
   */
  public function libraryEntitybuild(EntityTypeBuildEvent $event) {
    $event->getEntityTypes()['library']
      ->setFormClass('add', 'Drupal\libraries_provider_ui\Form\LibraryForm')
      ->setFormClass('edit', 'Drupal\libraries_provider_ui\Form\LibraryForm')
      ->setFormClass('delete', 'Drupal\libraries_provider_ui\Form\LibraryDeleteForm')
      ->setListBuilderClass('Drupal\libraries_provider_ui\LibraryListBuilder')
      ->setLinkTemplate('delete-form', '/admin/structure/libraries/manage/{library}/delete')
      ->setLinkTemplate('edit-form', '/admin/structure/libraries/manage/{library}')
      ->setLinkTemplate('collection', '/admin/structure/libraries')
      ->set('admin_permission', 'administer libraries');
  }

}
