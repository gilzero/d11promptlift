<?php

/**
 * @file
 * Theme and preprocess functions for page.
 */

use Drupal\artisan\customizations\ArtisanCustomizations;

/**
 * Implements hook_page_attachments_alter().
 */
function artisan_page_attachments_alter(array &$attachments) {
  $attachments['#attached']['html_head'][] = ArtisanCustomizations::getAttachmentStyles();
}

/**
 * Implements hook_theme_suggestions_page_alter().
 */
function artisan_theme_suggestions_page_alter(array &$suggestions) {
  // Node routes use "fullwidh" page suggestions to allow "edge to edge"
  // components when using layout builder for its or "full" (default fallback)
  // displays.
  // Under theme option active default?
  if (($node = \Drupal::routeMatch()->getParameter('node')) || ($node = \Drupal::routeMatch()->getParameter('node_preview'))) {
    $path_args = explode('/', trim(\Drupal::service('path.current')->getPath(), '/'));
    $suggestions[] = 'page__node__' . $node->bundle();
    $bundle = $node->bundle();
    // Detect display from layout builder without generating module dependency.
    $layoutBuilderEntityDisplayClassName = 'Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay';
    // Skip edit & remove pages.
    if (!(in_array('edit', $path_args) || in_array('delete', $path_args)) &&
      class_exists($layoutBuilderEntityDisplayClassName) &&
      ($display = $layoutBuilderEntityDisplayClassName::load('node.' . $bundle . '.full') ?? $layoutBuilderEntityDisplayClassName::load('node.' . $bundle . '.default')) &&
      is_a($layoutBuilderEntityDisplayClassName, $display::class, TRUE) &&
      $display->isLayoutBuilderEnabled()) {
      // Global fullwidth & per bundle suggestions.
      $suggestions[] = 'page__node__fullwidth';
      $suggestions[] = 'page__node__' . $node->bundle() . '__fullwidth';
    }
  }
}
