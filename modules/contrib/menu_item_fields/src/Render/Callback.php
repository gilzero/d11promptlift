<?php

namespace Drupal\menu_item_fields\Render;

use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Provides a trusted callbacks to alter some elements markup.
 *
 * @see menu_item_fields_preprocess_menu__field_content()
 */
class Callback implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['preRenderMenuLinkContent'];
  }

  /**
   * Fill the the link field with values from the menu item.
   *
   * #pre_render callback.
   */
  public static function preRenderMenuLinkContent($element) {
    // We skip processing if link field is not in display output.
    if (!isset($element['link'])) {
      return $element;
    }
    $contentLink = &$element['link'][0];
    $contentUrl = &$contentLink['#url'];

    // Set the title attribute (description field) from the menu item.
    $menuItemAttributes = $element['#menu_item']['url']->getOption('attributes');
    if (isset($menuItemAttributes['title'])) {
      $contentLinkAttributes = $contentUrl->getOption('attributes');
      $contentLinkAttributes['title'] = $menuItemAttributes['title'];
      $contentUrl->setOption('attributes', $contentLinkAttributes);
    }

    $contentUrl->setOption('set_active_class', $element['#menu_item']['url']->getOption('set_active_class'));

    if (is_string($contentLink['#title'])) {
      $contentLink['#title'] = $element['#menu_item']['title'];
    }

    return $element;
  }

}
