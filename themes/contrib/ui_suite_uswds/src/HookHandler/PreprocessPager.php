<?php

declare(strict_types=1);

namespace Drupal\ui_suite_uswds\HookHandler;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Template\Attribute;
use Drupal\ui_patterns\Plugin\UiPatterns\PropType\LinksPropType;

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
    $this->setLinksIconName($variables['items']);

    $before = LinksPropType::normalize(\array_filter([
      $variables['items']['previous'] ?? [],
      $variables['items']['first'] ?? [],
    ]));

    // Previous links in USWDS have a specific class.
    if (!empty($before)) {
      $before = $this->setPageLinkValues($before, TRUE);
    }

    // If ellipses between previous and numbered links.
    $ellipses_previous = [];
    if (isset($variables['ellipses']['previous']) && $variables['ellipses']['previous']) {
      $ellipses_previous[] = ['ellipses' => TRUE];
    }
    else {
      unset($before[1]);
    }

    $pages = LinksPropType::normalize($variables['items']['pages'] ?? []);
    if (isset($variables['current'])) {
      $current_page_index = (int) $variables['current'];
      foreach ($pages as $key => $page) {
        if ($page['title'] === $current_page_index) {
          unset($pages[$key]['url']);
          break;
        }
      }
    }

    $after = LinksPropType::normalize(array_filter([
      $variables['items']['last'] ?? [],
      $variables['items']['next'] ?? [],
    ]));

    $total = 'Last';
    if (isset($after[0])) {
      preg_match('/.*page=(\d*)/', $after[0]['url'], $output);
      if (isset($output[1])) {
        $total = (int) $output[1] + 1;
      }
    }

    // If ellipses between next and numbered links.
    $ellipses_next = [];
    if (isset($variables['ellipses']['next']) && $variables['ellipses']['next']) {
      $ellipses_next[] = ['ellipses' => TRUE];
    }
    else {
      unset($after[0]);
    }

    // Next links in USWDS have a specific class.
    if (!empty($after)) {
      $after = $this->setPageLinkValues($after);
    }

    $variables['items'] = array_merge($before, $ellipses_previous, $pages, $ellipses_next, $after);
    $variables['total_pages'] = (int) $total;
  }

  /**
   * If page link add certain USWDS values.
   *
   * @param array $items
   *   The items to check for.
   * @param bool $previous
   *   Is this a previous link.
   */
  protected function setPageLinkValues(array $items, bool $previous = FALSE): array {
    foreach ($items as $key => $item) {
      $previous ? $item['attributes']['class'][] = 'usa-pagination__previous-page' : $item['attributes']['class'][] = 'usa-pagination__next-page';
      $item['link'] = TRUE;
      $items[$key] = $item;
    }
    return $items;
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
   * Set USWDS icon name.
   *
   * @param array $items
   *   The items to check for.
   */
  protected function setLinksIconName(array &$items): void {
    foreach ($this->getLinksIcons() as $link_key => $icon_name) {
      if (isset($items[$link_key])) {
        $items[$link_key]['icon_name'] = $icon_name;
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

  /**
   * Get special links USWDS icon name.
   *
   * @return array
   *   The list of special links USWDS icon name.
   */
  protected function getLinksIcons(): array {
    return [
      'first' => 'navigate_far_before',
      'previous' => 'navigate_before',
      'next' => 'navigate_next',
      'last' => 'navigate_far_next',
    ];
  }

}
