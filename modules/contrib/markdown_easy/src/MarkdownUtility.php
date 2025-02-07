<?php

declare(strict_types=1);

namespace Drupal\markdown_easy;

/**
 * Utility methods for the Markdown Easy module.
 */
final class MarkdownUtility {

  /**
   * Returns the filter weights of the filters we care about.
   *
   * @param array<mixed> $filters
   *   Array of all filters for a given text format.
   *
   * @return array<string, integer|NULL>
   *   Array of weights for the filters we care about.
   */
  public function getFilterWeights(array $filters): array {
    $weight = [];
    $weight['filter_html'] = NULL;
    $weight['filter_autop'] = NULL;
    $weight['markdown_easy'] = NULL;
    foreach ($filters as $filter_key => $filter) {
      if ((bool) $filter['status']) {
        $filters[$filter_key] = $filter;
        // Limit HTML filter.
        if ($filter_key == 'filter_html') {
          $weight['filter_html'] = $filter['weight'];
        }
        // Convert line breaks filter.
        if ($filter_key == 'filter_autop') {
          $weight['filter_autop'] = $filter['weight'];
        }
        // Markdown Easy filter.
        if ($filter_key == 'markdown_easy') {
          $weight['markdown_easy'] = $filter['weight'];
        }
      }
    }
    return $weight;
  }

}
