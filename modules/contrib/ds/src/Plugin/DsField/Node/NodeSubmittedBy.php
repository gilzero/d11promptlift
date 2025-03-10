<?php

namespace Drupal\ds\Plugin\DsField\Node;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\ds\Attribute\DsField;
use Drupal\ds\Plugin\DsField\Date;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin that renders the submitted by field.
 */
#[DsField(
  id: 'node_submitted_by',
  title: new TranslatableMarkup('Submitted by'),
  entity_type: 'node',
  provider: 'node'
)]
class NodeSubmittedBy extends Date {

  /**
   * Drupal core Render service.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Constructs a Display Suite field plugin.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, Renderer $renderer, DateFormatterInterface $date_formatter, TimeInterface $time) {
    $this->renderer = $renderer;
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $date_formatter, $time);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('renderer'),
      $container->get('date.formatter'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $field = $this->getFieldConfiguration();

    /* @var $node \Drupal\node\NodeInterface */
    $node = $this->entity();

    /* @var $account \Drupal\user\UserInterface */
    $account = $node->getOwner();

    $date_format = str_replace('ds_post_date_', '', $field['formatter']);
    $user_name = [
      '#theme' => 'username',
      '#account' => $account,
    ];
    return [
      '#markup' => $this->t('Submitted by @user on @date.',
        [
          '@user' => $this->renderer->render($user_name),
          '@date' => $this->dateFormatter->format($this->entity()->created->value, $date_format),
        ]
      ),
      '#cache' => [
        'tags' => $account->getCacheTags(),
      ],
    ];
  }

}
