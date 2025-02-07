<?php

namespace Drupal\publication_date;

use Drupal\node\Entity\NodeType;
use Drupal\node\NodePermissions;

/**
 * Permissions to "Published On" field.
 */
class PublicationDateNodePermissions extends NodePermissions {

  /**
   * Returns an array of node type permissions.
   *
   * @return array
   *   The node type permissions.
   *   @see \Drupal\user\PermissionHandlerInterface::getPermissions()
   */
  protected function buildPermissions(NodeType $type): array {
    $type_id = $type->id();
    $type_params = ['%type_name' => $type->label()];

    return [
      "set $type_id published on date" => [
        'title' => $this->t('Modify %type_name "Published On" date.', $type_params),
        'description' => $this->t('Change the "Published On" date for this content type.'),
      ],
    ];
  }

}
