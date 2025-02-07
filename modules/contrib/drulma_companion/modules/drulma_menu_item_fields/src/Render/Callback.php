<?php

namespace Drupal\drulma_menu_item_fields\Render;

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
    return [
      'preRenderMenuLinkContent',
    ];
  }

  /**
   * Fill the the link with the appropiate class.
   *
   * #pre_render callback.
   */
  public static function preRenderMenuLinkContent($element) {

    $contentLink = &$element['link'];
    $contentUrl = &$contentLink[0]['#url'];

    $contentLinkAttributes = $contentUrl->getOption('attributes');
    $contentLinkAttributes['class'][] = 'navbar-item';
    $contentLinkAttributes['class'][] = 'navbar-drulma-adjust';
    $contentUrl->setOption('attributes', $contentLinkAttributes);

    $contentLink['#attributes']['class'][] = 'navbar-drulma-adjust';

    return $element;
  }

}
