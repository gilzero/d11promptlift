<?php

declare(strict_types=1);

namespace Drupal\ui_suite_bootstrap\HookHandler;

use Drupal\Component\Utility\SortArray;
use Drupal\Core\Access\AccessResultAllowed;

/**
 * Prepare local task link for component.
 */
class PreprocessMenuLocalTasks {

  /**
   * The possible local task types.
   *
   * @var string[]
   */
  public array $localTaskTypes = [
    'primary',
    'secondary',
  ];

  /**
   * Prepare local tasks for nav component.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocess(array &$variables): void {
    // Prepare structure for normalization.
    foreach ($this->localTaskTypes as $type) {
      $preparedLinks = [];
      $menuLocalTasks = $variables[$type];
      // Sort.
      \uasort($menuLocalTasks, [SortArray::class, 'sortByWeightProperty']);

      foreach ($menuLocalTasks as $menuLocalTask) {
        // Access check.
        if (isset($menuLocalTask['#access']) && !$menuLocalTask['#access'] instanceof AccessResultAllowed) {
          continue;
        }

        if ($menuLocalTask['#active']) {
          $menuLocalTask['#link']['url']->mergeOptions([
            'attributes' => [
              'class' => [
                'active',
              ],
            ],
          ]);
        }
        $preparedLinks[] = [
          'link' => [
            '#url' => $menuLocalTask['#link']['url'],
            '#options' => $menuLocalTask['#link']['localized_options'],
          ],
          'title' => $menuLocalTask['#link']['title'],
        ];
      }
      $variables['preprocessed_items_' . $type] = $preparedLinks;
    }
  }

}
