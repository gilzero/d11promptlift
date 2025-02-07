<?php

declare(strict_types=1);

namespace Drupal\ui_suite_bootstrap\HookHandler;

use Drupal\views\ViewExecutable;

/**
 * Handle CSS classes.
 */
class ViewsPreRender {

  /**
   * Handle CSS classes for media library.
   *
   * @param \Drupal\views\ViewExecutable $view
   *   The view.
   *
   * @see \claro_views_pre_render()
   */
  public function preRender(ViewExecutable $view): void {
    if ($view->id() === 'media_library') {
      if ($view->current_display === 'widget') {
        if (\array_key_exists('media_library_select_form', $view->field)) {
          $this->addClasses($view->field['media_library_select_form']->options['element_wrapper_class'], [
            'position-absolute',
            'ms-1',
            'z-1',
          ]);
        }
      }
    }
  }

  /**
   * Add classes.
   *
   * @param string $option
   *   The existing option.
   * @param string[] $classesToAdd
   *   The classes to add.
   */
  protected function addClasses(string &$option, array $classesToAdd): void {
    $classes = \preg_split('/\s+/', $option);
    if (!\is_array($classes)) {
      return;
    }

    $classes = \array_filter($classes);
    $classes = \array_merge($classes, $classesToAdd);
    $option = \implode(' ', \array_unique($classes));
  }

}
