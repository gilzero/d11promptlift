<?php

namespace Drupal\ai_automator_google_vision\Plugin\AiAutomatorType;

use Drupal\ai_automators\Attribute\AiAutomatorType;
use Drupal\ai_automator_google_vision\PluginBase\DetectTextBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The rules for a text_long field.
 */
#[AiAutomatorType(
  id: 'ai_automator_google_vision_detect_text_to_text_long',
  label: new TranslatableMarkup('Google Vision: Detect Text'),
  field_rule: 'text_long',
  target: '',
)]
class DetectTextToTextLong extends DetectTextBase {

  /**
   * {@inheritDoc}
   */
  public $title = 'Google Vision: Detect Text';

}
