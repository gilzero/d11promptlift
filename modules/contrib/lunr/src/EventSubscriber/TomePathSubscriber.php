<?php

namespace Drupal\lunr\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileUrlGenerator;
use Drupal\tome_static\Event\CollectPathsEvent;
use Drupal\tome_static\Event\TomeStaticEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds index filenames for Tome exports.
 */
class TomePathSubscriber implements EventSubscriberInterface {

  /**
   * The Lunr search entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $lunrSearchStorage;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The file url generator.
   * @var \Drupal\Core\File\FileUrlGenerator
   */
  protected $fileUrlGenerator;

  /**
   * Constructs the EntityPathSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   * @param \Drupal\Core\File\FileUrlGenerator $file_url_generator
   *   The file url generator.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, FileSystemInterface $file_system, FileUrlGenerator $file_url_generator) {
    $this->lunrSearchStorage = $entity_type_manager->getStorage('lunr_search');
    $this->fileSystem = $file_system;
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * Reacts to a collect paths event.
   *
   * @param \Drupal\tome_static\Event\CollectPathsEvent $event
   *   The collect paths event.
   */
  public function collectPaths(CollectPathsEvent $event) {
    /** @var \Drupal\lunr\LunrSearchInterface $search */
    foreach ($this->lunrSearchStorage->loadMultiple() as $search) {
      $directory = dirname($search->getBaseIndexPath());
      if (!file_exists($directory)) {
        continue;
      }
      foreach (array_keys($this->fileSystem->scanDirectory($directory, '/.*/')) as $filename) {
        $event->addPath($this->fileUrlGenerator->generateAbsoluteString($filename), ['language_processed' => 'language_processed']);
      }
    }
    $event->addPath(\Drupal::service('extension.list.module')->getPath('lunr') . '/js/search.worker.js', ['language_processed' => 'language_processed']);
    $event->addPath(\Drupal::service('extension.list.module')->getPath('lunr') . '/js/vendor/lunr/lunr.min.js', ['language_processed' => 'language_processed']);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[TomeStaticEvents::COLLECT_PATHS][] = ['collectPaths'];
    return $events;
  }

}
