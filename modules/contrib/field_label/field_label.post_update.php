<?php

/**
 * @file
 * Post update functions for field_label.
 */

/**
 * Update settings to include plural_label_enabled key.
 */
function field_label_post_update_plural_label() {
  $config = \Drupal::configFactory()->getEditable('field_label.settings');
  $config->set('plural_label_enabled', FALSE);
  $config->save();
}
