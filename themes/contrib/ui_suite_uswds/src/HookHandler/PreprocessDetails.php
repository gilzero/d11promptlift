<?php

declare(strict_types=1);

namespace Drupal\ui_suite_uswds\HookHandler;

use Drupal\Component\Utility\Html;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\Element;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Preprocess hook for details.
 */
class PreprocessDetails implements ContainerInjectionInterface {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   */
  public function __construct(ModuleHandlerInterface $moduleHandler) {
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    // @phpstan-ignore-next-line
    return new static(
      $container->get('module_handler')
    );
  }

  /**
   * Ensure breadcrumb structure fits into links prop structure.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocess(array &$variables): void {
    // Add the necessary class to the summary (which we'll render as a button).
    $variables['summary_attributes']->addClass('usa-accordion-button');

    // Check to see if this should be un-collapsible. This could be the case if
    // the element has errors in it, or if it has been specifically
    // flagged as such.
    $uncollapsible = !empty($variables['element']['#ui_suite_uswds_uncollapsible']);
    if (!$uncollapsible) {
      $inline_form_errors = $this->moduleHandler->moduleExists('inline_form_errors');
      if ($inline_form_errors) {
        $uncollapsible = $this->isErrorInArray($variables['element']);
      }
    }
    if ($uncollapsible) {
      $variables['element']['#open'] = TRUE;
      $variables['uncollapsible'] = TRUE;
    }
    else {
      // Otherwise is this is going to be collapsible, we need to force a title.
      if (empty($variables['title'])) {
        $variables['title'] = '&nbsp;';
      }
    }

    // Drupal defaults to open/expanded details, unless '#open' is FALSE, so we
    // have to match that, even though it would be nice to default to closed.
    $open = 'true';
    if (isset($variables['element']['#open']) && !$variables['element']['#open']) {
      $open = 'false';
    }
    $variables['summary_attributes']['aria-expanded'] = $open;

    if (empty($variables['attributes']['id'])) {
      $variables['attributes']['id'] = Html::getUniqueId('uswds-accordion');
    }
  }

  /**
   * Helper method to find errors in a render array.
   */
  protected function isErrorInArray($element): bool {
    if (!empty($element['#errors'])) {
      return TRUE;
    }
    foreach (Element::children($element) as $key) {
      if ($this->isErrorInArray($element[$key])) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
