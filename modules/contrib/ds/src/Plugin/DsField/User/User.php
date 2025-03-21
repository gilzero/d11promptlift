<?php

namespace Drupal\ds\Plugin\DsField\User;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\ds\Attribute\DsField;
use Drupal\ds\Plugin\DsField\Entity;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin that renders a view mode.
 */
#[DsField(
  id: 'user',
  title: new TranslatableMarkup('User'),
  entity_type: 'node',
  provider: 'user'
)]
class User extends Entity {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, EntityDisplayRepositoryInterface $entity_display_repository, EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;

    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_display_repository);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_display.repository'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $view_mode = $this->getEntityViewMode();

    /* @var $node \Drupal\node\NodeInterface */
    $node = $this->entity();
    $uid = $node->getOwnerId();
    $user = $this->entityTypeManager
      ->getStorage('user')
      ->load($uid);

    $build = [];
    if ($user) {
      $build = $this->entityTypeManager
        ->getViewBuilder('user')
        ->view($user, $view_mode);
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function linkedEntity() {
    return 'user';
  }

}
