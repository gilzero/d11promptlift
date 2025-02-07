<?php

/**
 * @file
 * Funtions to alter links (menu, field type, etc).
 *
 * FUTURE-TODO-WhenSdcApiIsReady Use SDC data transformer when exists,
 * for now... preprocess.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;

/**
 * Implements hook_preprocess_file_link().
 */
function artisan_starterkit_site_preprocess_file_link(&$variables) {
  // When a file is uploaded by anonymous
  // the link only contains the name of the file.
  if (isset($variables['link']) && is_array($variables['link'])) {
    // File link open new window/tab by default & download.
    if (($variables['link']['#attributes'] ?? NULL) instanceof Attribute) {
      $variables['link']['#attributes']->setAttribute('target', '_blank');
      // @code $variables['link']['#attributes']->setAttribute('download', $variables['link']['#title']); @endcode
    }
    else {
      $variables['link']['#attributes']['target'] = '_blank';
      $variables['link']['#attributes']['download'] = $variables['link']['#title'];
    }
  }
}

/**
 * Implements hook_preprocess_field__link().
 */
function artisan_starterkit_site_preprocess_field__link(&$variables) {
  // Open new tab when external, auto-detect.
  foreach (Element::children($variables['items']) as $delta) {
    if (!empty($variables['items'][$delta]['content']) &&
      $variables['items'][$delta]['content']['#type'] == 'link' &&
      $variables['items'][$delta]['content']['#url'] instanceof Url &&
      $variables['items'][$delta]['content']['#url']->isExternal()) {
      if (($variables['items'][$delta]['content']['#options']['attributes'] ?? NULL) instanceof Attribute) {
        $variables['items'][$delta]['content']['#options']['attributes']->setAttribute('target', '_blank');
      }
      else {
        $variables['items'][$delta]['content']['#options']['attributes']['target'] = '_blank';
      }
    }
  }
}

/**
 * Implements hook_preprocess_menu().
 */
function artisan_starterkit_site_preprocess_menu(&$variables) {
  // Open new tab when external, auto-detect.
  _artisan_starterkit_site_preprocess_menu_level($variables['items']);
}

/**
 * Open new tab when external external link at all levels.
 */
function _artisan_starterkit_site_preprocess_menu_level(&$items) {
  foreach (Element::children($items) as $delta) {
    if (!empty($items[$delta]) &&
      $items[$delta]['url'] instanceof Url &&
      $items[$delta]['url']->isExternal()) {
      $items[$delta]['url']->mergeOptions(['attributes' => ['target' => '_blank']]);
    }
    if (!empty($items[$delta]['below'])) {
      _artisan_starterkit_site_preprocess_menu_level($items[$delta]['below']);
    }
  }
}
