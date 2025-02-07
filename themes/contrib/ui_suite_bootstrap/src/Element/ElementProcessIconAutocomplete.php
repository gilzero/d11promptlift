<?php

declare(strict_types=1);

namespace Drupal\ui_suite_bootstrap\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Theme\Icon\IconDefinition;
use Drupal\Core\Theme\Icon\IconDefinitionInterface;
use Drupal\ui_suite_bootstrap\Utility\Element;

/**
 * Element Process methods for icon autocomplete.
 */
class ElementProcessIconAutocomplete {

  /**
   * Processes element supporting icon autocomplete.
   *
   * Can't do that in a preprocess hook because the input group mechanism
   * on other elements is in a #process so triggered before all preprocesses.
   */
  public static function processIconAutocomplete(array &$element, FormStateInterface $form_state, array &$complete_form): array {
    $packId = '';
    $iconId = '';

    if (isset($element['icon_id']['#value'])) {
      $icon_data = IconDefinition::getIconDataFromId($element['icon_id']['#value']);
      if (!$icon_data) {
        return $element;
      }
      $packId = $icon_data['pack_id'];
      $iconId = $icon_data['icon_id'];
    }
    elseif (isset($element['#value']['object']) && $element['#value']['object'] instanceof IconDefinitionInterface) {
      $packId = $element['#value']['object']->getPackId();
      $iconId = $element['#value']['object']->getId();
    }
    elseif (!empty($element['#default_value'])) {
      $icon_data = IconDefinition::getIconDataFromId($element['#default_value']);
      if (!$icon_data) {
        return $element;
      }
      $packId = $icon_data['pack_id'];
      $iconId = $icon_data['icon_id'];
    }

    if (empty($packId) || empty($iconId)) {
      return $element;
    }

    $element_object = Element::create($element);
    if (!isset($element_object->icon_id)) {
      return $element;
    }

    /** @var \Drupal\ui_icons\Template\IconPreviewTwigExtension $iconPreviewTwig */
    $iconPreviewTwig = \Drupal::service('ui_icons.twig_extension');
    $previewIcon = $iconPreviewTwig->getIconPreview($packId, $iconId);
    $element_object->icon_id->setProperty('input_group_before', [
      // Need to render the icon so ElementProcessInputGroup::processAddon()
      // will wrap the icon in a span with the appropriate attributes.
      Element::create($previewIcon)->renderPlain(),
    ]);
    return $element;
  }

}
