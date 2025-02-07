<?php

namespace Drupal\ai_automator_google_vision\Plugin\AiAutomatorType;

use Drupal\ai_automators\Attribute\AiAutomatorType;
use Drupal\ai_automator_google_vision\PluginBase\DetectTextBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The rules for a string_long field.
 */
#[AiAutomatorType(
  id: 'ai_automator_google_vision_detect_text_to_string_long',
  label: new TranslatableMarkup('Google Vision: Detect Text'),
  field_rule: 'string_long',
  target: '',
)]
class DetectTextToStringLong extends DetectTextBase {

  /**
   * {@inheritDoc}
   */
  public $title = 'Google Vision: Detect Text';

}
