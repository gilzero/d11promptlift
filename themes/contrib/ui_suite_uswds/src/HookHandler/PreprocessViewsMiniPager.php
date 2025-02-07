<?php

declare(strict_types=1);

namespace Drupal\ui_suite_uswds\HookHandler;

use Drupal\ui_patterns\Plugin\UiPatterns\PropType\LinksPropType;

/**
 * Preprocess hook for views mini pager.
 */
class PreprocessViewsMiniPager extends PreprocessPager {

  /**
   * Ensure views mini pager structure fits into links prop structure.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocess(array &$variables): void {
    if (!isset($variables['items'])) {
      return;
    }
    $this->setLinksAriaLabel($variables['items']);

    $before = LinksPropType::normalize(array_filter([
      $variables['items']['previous'] ?? [],
    ]));

    // Previous links in USWDS have a specific class.
    if (!empty($before)) {
      $before = $this->setPageLinkValues($before, TRUE);
    }

    $pages = LinksPropType::normalize(array_filter([
      ['title' => $variables['items']['current']],
    ]));

    $after = LinksPropType::normalize(array_filter([
      $variables['items']['next'] ?? [],
    ]));

    // Next links in USWDS have a specific class.
    if (!empty($after)) {
      $after = $this->setPageLinkValues($after);
    }

    if (!empty($before) || !empty($after)) {
      $variables['items'] = array_merge($before, $pages, $after);
    }
  }

}
