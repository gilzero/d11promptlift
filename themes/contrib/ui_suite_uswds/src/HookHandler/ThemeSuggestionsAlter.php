<?php

declare(strict_types=1);

namespace Drupal\ui_suite_uswds\HookHandler;

use Drupal\block\Entity\Block;

/**
 * Add theme suggestions.
 */
class ThemeSuggestionsAlter {

  use UswdsSharedTrait;

  /**
   * Template suggestions for form element.
   *
   * @param array $suggestions
   *   The list of suggestions.
   * @param array $variables
   *   The theme variables.
   */
  public function formElement(array &$suggestions, array $variables): void {
    $suggestions = ['form_element__type__' . $variables['element']['#type']];
    if (isset($variables['element']['#form_id'])) {
      $suggestions[] = 'form_element__form_id__' . $variables['element']['#form_id'];
      $suggestions[] = 'form_element__' . $variables['element']['#form_id'] . '__' . $variables['element']['#type'];
    }
  }

  /**
   * Template suggestions for form element label.
   *
   * @param array $suggestions
   *   The list of suggestions.
   * @param array $variables
   *   The theme variables.
   */
  public function formElementLabel(array &$suggestions, array $variables): void {
    if (isset($variables['element']['#form_element_type'])) {
      $suggestions[] = 'form_element_label__form_type__' . $variables['element']['#form_element_type'];
    }
    if (isset($variables['element']['#form_id'])) {
      $suggestions[] = 'form_element_label__form_id__' . $variables['element']['#form_id'];
    }
    if (isset($variables['element']['#form_element_type']) && isset($variables['element']['#form_id'])) {
      $suggestions[] = 'form_element_label__' . $variables['element']['#form_id'] . '__' . $variables['element']['#form_element_type'];
    }

    if (isset($variables['element']['#element_type'])) {
      $suggestions[] = 'form_element_label__type__' . $variables['element']['#element_type'];
    }
    if (isset($variables['element']['#id'])) {
      $suggestions[] = 'form_element_label__id__' . str_replace('-', '_', $variables['element']['#id']);
    }
    if (isset($variables['element']['#element_type']) && isset($variables['element']['#id'])) {
      $suggestions[] = 'form_element_label__' . str_replace('-', '_', $variables['element']['#id']) . '__' . $variables['element']['#element_type'];
    }
  }

  /**
   * Template suggestions for input.
   *
   * @param array $suggestions
   *   The list of suggestions.
   * @param array $variables
   *   The theme variables.
   */
  public function input(array &$suggestions, array $variables): void {
    if (in_array($variables['element']['#type'], ['checkbox', 'radio']) && $variables['element']['#attributes']['type'] !== 'hidden' && isset($variables['element']['id'])) {
      $suggestions[] = 'input__' . $variables['element']['#type'] . '__' . str_replace('-', '_', $variables['element']['id']);
    }
  }

  /**
   * Template suggestions for forms.
   *
   * @param array $suggestions
   *   The list of suggestions.
   * @param array $variables
   *   The theme variables.
   */
  public function form(array &$suggestions, array $variables): void {
    if ($variables['element']['#form_id'] === 'search_form') {
      $suggestions[] = 'form__search_block_form';
    }
  }

  /**
   * Template suggestions for forms.
   *
   * @param array $suggestions
   *   The list of suggestions.
   * @param array $variables
   *   The theme variables.
   */
  public function menu(array &$suggestions, array $variables): void {
    if (!empty($variables['items'])) {
      foreach ($variables['items'] as $item) {
        if (!empty($item['#ui_suite_uswds_region'])) {
          if ($this->processMenuRegion($item['#ui_suite_uswds_region'])) {
            $suggestions[] = 'menu__' . $item['#ui_suite_uswds_region'];
          }
        }
        // We only need to look at one.
        break;
      }
    }
  }

  /**
   * Template suggestions for blocks.
   *
   * @param array $suggestions
   *   The list of suggestions.
   * @param array $variables
   *   The theme variables.
   */
  public function block(array &$suggestions, array $variables): void {
    // Set the block variable.
    $block = '';
    if (!empty($variables['elements']['#id'])) {
      $block = Block::load($variables['elements']['#id']);
    }

    if (!empty($block)) {
      // We need to suggest our custom theming for menu blocks in certain
      // regions.
      if (in_array('block__system_menu_block', $suggestions)) {
        $menu_regions =
          [
            'primary_menu',
            'secondary_menu',
            'sidebar_first',
            'sidebar_second',
            'footer_primary',
            'footer_secondary',
          ];
        if (in_array($block->getRegion(), $menu_regions)) {
          if ($this->processMenuRegion($block->getRegion())) {
            $suggestions[] = 'block__system_menu_block__' . $block->getRegion();
          }
        }
      }

      // We need to suggest our custom theming for menu blocks in certain
      // regions.
      if (in_array('block__block_content', $suggestions)) {
        $bc_regions = ['hero'];
        if (in_array($block->getRegion(), $bc_regions)) {
          $suggestions[] = 'block__block_content__' . $block->getRegion();
        }
      }
    }
  }

  /**
   * Template suggestions for details.
   *
   * @param array $suggestions
   *   The list of suggestions.
   * @param array $variables
   *   The theme variables.
   */
  public function details(array &$suggestions, array $variables): void {
    if (
      (isset($variables['element']['#form_id']) && $variables['element']['#form_id'] === 'layout_builder_configure_section') ||
      (isset($variables['element']['#attributes']['data-drupal-selector']) && str_starts_with($variables['element']['#attributes']['data-drupal-selector'], 'edit-layout-settings-'))) {
      array_splice($suggestions, 1, 0, 'details_layout_builder');
    }
  }

}
