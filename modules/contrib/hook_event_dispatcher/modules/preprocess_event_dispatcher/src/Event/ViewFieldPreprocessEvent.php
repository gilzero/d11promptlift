<?php

namespace Drupal\preprocess_event_dispatcher\Event;

/**
 * Class ViewFieldPreprocessEvent.
 *
 * @method \Drupal\preprocess_event_dispatcher\Variables\ViewFieldEventVariables getVariables()
 */
final class ViewFieldPreprocessEvent extends AbstractPreprocessEvent {

  /**
   * {@inheritdoc}
   */
  public static function getHook(): string {
    return 'views_view_field';
  }

}
