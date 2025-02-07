<?php

declare(strict_types=1);

namespace Drupal\ui_suite_bootstrap\Element;

use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\Url;
use Drupal\ui_suite_bootstrap\Utility\Bootstrap;
use Drupal\ui_suite_bootstrap\Utility\Element;

/**
 * Element Prerender methods for layout builder.
 */
class ElementPreRenderLayoutBuilder implements TrustedCallbackInterface {

  /**
   * Weight to ensure the section label is placed first.
   */
  public const SECTION_LABEL_WEIGHT = -1;

  /**
   * Handle styling for layout builder element.
   */
  public static function preRenderLayoutBuilder(array $element): array {
    $element_object = Element::create($element);

    if (!isset($element_object->layout_builder)) {
      return $element;
    }

    // Main wrapper.
    $element_object->layout_builder->addClass([
      'border',
      'border-3',
      'border-primary',
      'p-4',
      'pb-2',
    ]);

    /** @var \Drupal\ui_suite_bootstrap\Utility\Element $layoutBuilderArea */
    foreach ($element_object->layout_builder->children() as $layoutBuilderArea) {
      // Add section.
      if (isset($layoutBuilderArea->link) && $layoutBuilderArea->link->isType('link')) {
        $url = $layoutBuilderArea->link->getProperty('url');
        if ($url instanceof Url && $url->getRouteName() == 'layout_builder.choose_section') {
          // Wrapper.
          $layoutBuilderArea->addClass([
            'mb-3',
            'py-4',
            'text-center',
            'bg-light',
          ]);

          // Link.
          $layoutBuilderArea->link->addClass([
            'btn',
            'btn-secondary',
          ]);
          $layoutBuilderArea->link->setIcon(Bootstrap::icon('plus-lg'));
        }
      }

      // Section label.
      // Display section label first. So we can display action links with icon
      // only.
      if (isset($layoutBuilderArea->section_label)) {
        $layoutBuilderArea->section_label->setProperty('access', TRUE);
        $layoutBuilderArea->section_label->setProperty('weight', static::SECTION_LABEL_WEIGHT);
      }

      // Remove link.
      if (isset($layoutBuilderArea->remove) && $layoutBuilderArea->remove->isType('link')) {
        $url = $layoutBuilderArea->remove->getProperty('url');
        if ($url instanceof Url && $url->getRouteName() == 'layout_builder.remove_section') {
          $layoutBuilderArea->remove->addClass('mx-1');
          $layoutBuilderArea->remove->setIcon(Bootstrap::icon('trash'));
          $layoutBuilderArea->remove->setProperty('icon_position', 'icon_only');
        }
      }

      // Configure link.
      if (isset($layoutBuilderArea->configure) && $layoutBuilderArea->configure->isType('link')) {
        $url = $layoutBuilderArea->configure->getProperty('url');
        if ($url instanceof Url && $url->getRouteName() == 'layout_builder.configure_section') {
          $layoutBuilderArea->configure->addClass('mx-1');
          $layoutBuilderArea->configure->setIcon(Bootstrap::icon('pencil-fill'));
          $layoutBuilderArea->configure->setProperty('icon_position', 'icon_only');
        }
      }

      // Section.
      if (isset($layoutBuilderArea->{'layout-builder__section'})) {
        $section = $layoutBuilderArea->{'layout-builder__section'};
        foreach ($section->children() as $region) {
          // Region label.
          if (isset($region->region_label)) {
            /** @var \Drupal\ui_suite_bootstrap\Utility\Element $regionLabel */
            $regionLabel = $region->region_label;
            $regionLabel->addClass('text-bg-light');
          }

          // Add block.
          if (isset($region->layout_builder_add_block)) {
            /** @var \Drupal\ui_suite_bootstrap\Utility\Element $addBlockArea */
            $addBlockArea = $region->layout_builder_add_block;
            if (isset($addBlockArea->link) && $addBlockArea->link->isType('link')) {
              $url = $addBlockArea->link->getProperty('url');
              if ($url instanceof Url && $url->getRouteName() == 'layout_builder.choose_block') {
                // Wrapper.
                $addBlockArea->addClass([
                  'py-4',
                  'text-center',
                  'bg-primary-subtle',
                ]);

                // Link.
                $addBlockArea->link->addClass([
                  'btn',
                  'btn-primary',
                ]);
                $addBlockArea->link->setIcon(Bootstrap::icon('plus-lg'));
              }
            }
          }
        }
      }

      // Section Library: Add this template to library.
      if (isset($layoutBuilderArea->add_template_to_library) && $layoutBuilderArea->add_template_to_library->isType('link')) {
        $url = $layoutBuilderArea->add_template_to_library->getProperty('url');
        if ($url instanceof Url && $url->getRouteName() == 'section_library.add_template_to_library') {
          $layoutBuilderArea->add_template_to_library->addClass([
            'btn',
            'btn-outline-secondary',
          ]);
          $layoutBuilderArea->add_template_to_library->setIcon(Bootstrap::icon('folder-plus'));

          $layoutBuilderArea->add_template_to_library->appendProperty('theme_wrappers', [
            'container' => [
              '#attributes' => [
                'class' => [
                  'mb-3',
                  'text-center',
                ],
              ],
            ],
          ]);
        }
      }

      // Section Library: Import from library.
      if (isset($layoutBuilderArea->choose_template_from_library) && $layoutBuilderArea->choose_template_from_library->isType('link')) {
        $url = $layoutBuilderArea->choose_template_from_library->getProperty('url');
        if ($url instanceof Url && $url->getRouteName() == 'section_library.choose_template_from_library') {
          $layoutBuilderArea->choose_template_from_library->addClass([
            'btn',
            'btn-outline-secondary',
            'ms-3',
          ]);
          $layoutBuilderArea->choose_template_from_library->setIcon(Bootstrap::icon('download'));
        }
      }

      // Section Library: Add to library.
      if (isset($layoutBuilderArea->add_to_library) && $layoutBuilderArea->add_to_library->isType('link')) {
        $url = $layoutBuilderArea->add_to_library->getProperty('url');
        if ($url instanceof Url && $url->getRouteName() == 'section_library.add_section_to_library') {
          $layoutBuilderArea->add_to_library->addClass('mx-1');
          $layoutBuilderArea->add_to_library->setIcon(Bootstrap::icon('folder-plus'));
          $layoutBuilderArea->add_to_library->setProperty('icon_position', 'icon_only');
        }
      }
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks(): array {
    return ['preRenderLayoutBuilder'];
  }

}
