<?php

declare(strict_types=1);

namespace Drupal\ui_suite_bootstrap\HookHandler;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Template\Attribute;

/**
 * Ensure pager structure fits into links prop structure.
 */
class PreprocessPager {

  use StringTranslationTrait;

  /**
   * Ensure pager structure fits into link prop structure.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocess(array &$variables): void {
    if (!isset($variables['items'])) {
      return;
    }
    $this->setLinksAriaLabel($variables['items']);

    $pages = $variables['items']['pages'] ?? [];
    foreach ($pages as $key => $page) {
      // Set item text now, as the array_merge will reorder items.
      $pages[$key]['text'] = $key;
      // The current item should not be a link.
      if (isset($variables['current']) && $key == $variables['current']) {
        unset($pages[$key]['href']);
      }
    }

    $variables['preprocessed_items'] = \array_merge(\array_filter([
      $variables['items']['first'] ?? [],
      $variables['items']['previous'] ?? [],
    ]), $pages, \array_filter([
      $variables['items']['next'] ?? [],
      $variables['items']['last'] ?? [],
    ]));
  }

  /**
   * Set aria-label attribute.
   *
   * @param array $items
   *   The items to check for.
   */
  protected function setLinksAriaLabel(array &$items): void {
    foreach ($this->getLinksAriaLabel() as $link_key => $aria_label) {
      if (isset($items[$link_key]['attributes']) && $items[$link_key]['attributes'] instanceof Attribute) {
        $items[$link_key]['attributes']->setAttribute('aria-label', $aria_label);
      }
    }
  }

  /**
   * Get special links aria label.
   *
   * @return array
   *   The list of special links aria label.
   */
  protected function getLinksAriaLabel(): array {
    return [
      'first' => $this->t('First'),
      'previous' => $this->t('Previous'),
      'next' => $this->t('Next'),
      'last' => $this->t('Last'),
    ];
  }

}
