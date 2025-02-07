<?php

declare(strict_types=1);

namespace Drupal\ui_suite_bootstrap\HookHandler;

use Drupal\ui_patterns\Plugin\UiPatterns\PropType\LinksPropType;

/**
 * Ensure links structure fits into list group structure.
 */
class PreprocessLinksMediaLibraryMenu {

  /**
   * Ensure links structure fits into list group structure.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocess(array &$variables): void {
    if (empty($variables['links'])) {
      return;
    }

    $variables['preprocessed_items'] = LinksPropType::normalize(\array_filter(
      $variables['links'],
    ));
  }

}
