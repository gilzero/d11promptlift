<?php

/**
 * @file
 * Theme and preprocess functions for pages.
 */

use Drupal\node\NodeInterface;

/**
 * Implements hook_preprocess_page().
 */
function artisan_starterkit_preprocess_page(&$variables) {
  // Create a boolean "field_distraction_free" field & add to nodes with
  // possibility just show main page content without header & footer.
  // Suggestion:
  // - Label: Distraction free.
  // - Description: Hide page header & footer when active.
  // @see Page "artisan:page" component.
  if (!empty($variables['node']) && $variables['node'] instanceof NodeInterface) {
    $variables['distraction_free'] = $variables['node']->hasField('field_distraction_free') && !$variables['node']->get('field_distraction_free')->isEmpty() ? (bool) $variables['node']->get('field_distraction_free')->value : FALSE;
  }
}
