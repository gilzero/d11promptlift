<?php

namespace Drupal\ds\Plugin\DsField\Media;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\ds\Attribute\DsField;
use Drupal\ds\Plugin\DsField\Title;

/**
 * Plugin that renders the title of a media item.
 */
#[DsField(
  id: 'media_name',
  title: new TranslatableMarkup('Name'),
  entity_type: 'media',
  provider: 'media'
)]
class MediaName extends Title {

  /**
   * {@inheritdoc}
   */
  public function entityRenderKey() {
    return 'name';
  }

}
