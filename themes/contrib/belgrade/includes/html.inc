<?php

/**
 * @file
 * Theme and preprocess functions for html wrapper.
 */

use Drupal\node\NodeInterface;

/**
 *
 */
function belgrade_preprocess_html(&$variables) {
  $node = \Drupal::routeMatch()->getParameter('node');
  if ($node instanceof NodeInterface) {
    // * Add content type machine name body class.
    $variables['attributes']['class'][] = 'node-type--' . str_replace('_', '-', $node->bundle());
  }

  // * Check different error conditions to add body classes.
  $route_name = \Drupal::request()->attributes->get('_route');
  if ('system.404' === $route_name) {
    $variables['attributes']['class'][] = 'error404';
  }
  if ('system.403' === $route_name) {
    $variables['attributes']['class'][] = 'error403';
  }

  // * Colors.
  if (theme_get_setting('belgrade_enable_color')) {
    $variables['#attached']['library'][] = 'belgrade/colors';

    $base_primary_color_hex = theme_get_setting('base_primary_color') ?? '#41449f';
    $base_light_color_hex = theme_get_setting('base_light_color') ?? '#E9E9f2';
    $base_dark_color_hex = theme_get_setting('base_dark_color') ?? '#272727';
    $body_text_color_hex = theme_get_setting('body_text_color') ?? '#333333';
    $body_background_color_hex = theme_get_setting('body_background_color') ?? '#FFFFFF';

    $styles = [
      "--beo-primary: $base_primary_color_hex;",
      "--beo-primary-rgb:" . _hex2rgb($base_primary_color_hex) . ";",
      "--beo-light: $base_primary_color_hex;",
      "--beo-light-rgb:" . _hex2rgb($base_light_color_hex) . ";",
      "--beo-dark: $base_dark_color_hex;",
      "--beo-dark-rgb:" . _hex2rgb($base_dark_color_hex) . ";",
      "--beo-body-bg: $body_background_color_hex;",
      "--beo-body-color: $body_text_color_hex;",
    ];

    $variables['html_attributes']->setAttribute('style', implode('', $styles));
  }

  // * Get theme path in JS
  $variables['#attached']['drupalSettings']['path']['themeUrl'] = \Drupal::theme()->getActiveTheme()->getPath();
}

/**
 * Covert hex to rgb color.
 */
function _hex2rgb( $color ) {
  list($r, $g, $b) = sscanf($color, "#%02x%02x%02x");
  $output = "$r, $g, $b";
  return $output;
}

/**
 * Covert hex to rgba color.
 */
function _hex2rgba( $color, $opacity ) {
  list($r, $g, $b) = sscanf($color, "#%02x%02x%02x");
  $output = "rgba($r, $g, $b, $opacity)";
  return $output;
}
