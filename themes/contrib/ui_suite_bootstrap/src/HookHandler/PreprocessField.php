<?php

declare(strict_types=1);

namespace Drupal\ui_suite_bootstrap\HookHandler;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add variables to preserve behavior when Fences is not enabled.
 */
class PreprocessField implements ContainerInjectionInterface {

  public function __construct(
    protected ModuleHandlerInterface $moduleHandler,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('module_handler'),
    );
  }

  /**
   * Add variables to preserve behavior when Fences is not enabled.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocess(array &$variables): void {
    if ($this->moduleHandler->moduleExists('fences')) {
      return;
    }

    $variables['display_field_tag'] = TRUE;
    $variables['display_label_tag'] = TRUE;
    $variables['display_items_wrapper_tag'] = TRUE;
    $variables['display_item_tag'] = TRUE;

    if ($variables['label_display'] == 'inline') {
      $variables['title_attributes']['class'][] = 'fw-bold';
      $variables['attributes']['class'][] = 'clearfix';
      $variables['title_attributes']['class'][] = 'float-start';
      $variables['title_attributes']['class'][] = 'pe-2';
      $variables['field_items_wrapper_attributes']['class'][] = 'float-start';
    }
    elseif ($variables['label_display'] == 'above') {
      $variables['title_attributes']['class'][] = 'fw-bold';
    }
  }

}
