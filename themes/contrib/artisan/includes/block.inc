<?php

/**
 * @file
 * Theme and preprocess functions for blocks.
 */

/**
 * Implements hook_preprocess_links__language_block().
 */
function artisan_preprocess_links__language_block(&$variables) {
  $variables['current_language_name'] = \Drupal::languageManager()->getCurrentLanguage()->getName();
  $variables['current_language_id'] = \Drupal::languageManager()->getCurrentLanguage()->getId();
}

/**
 * Implements hook_theme_suggestions_block_alter().
 */
function artisan_theme_suggestions_block_alter(array &$suggestions, array $variables) {
  if ($variables['elements']['#plugin_id'] == 'local_tasks_block') {
    $node = \Drupal::routeMatch()->getParameter('node');
    $term = \Drupal::routeMatch()->getParameter('taxonomy_term');
    if (!empty($node) || !empty($term)) {
      $suggestions[] = 'block__local_tasks_block__floating';
    }
  }
}
