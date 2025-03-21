<?php

declare(strict_types=1);

namespace Drupal\ui_patterns\Template;

use Drupal\Core\Template\Attribute;
use Drupal\Core\Template\AttributeHelper;

/**
 * Methods for the set_attribute and add_class filters.
 *
 * In a trait, to be usable in other places.
 */
trait AttributesFilterTrait {

  /**
   * Add a value into the class attributes of a given element.
   *
   * @param mixed $element
   *   A render array.
   * @param string[]|string ...$classes
   *   The classes to add on element. Arguments can include string keys directly
   *   or arrays of string keys.
   *
   * @return mixed
   *   The element with the given class(es) in attributes or the unchanged
   *   element if passed value is not an array.
   *
   * @see Drupal\Core\Template\TwigExtension::addClass()
   */
  public function addClass(mixed $element, ...$classes): mixed {
    if (!\is_array($element)) {
      return $element;
    }
    if (\array_is_list($element)) {
      foreach ($element as $index => $item) {
        if (!\is_array($item)) {
          continue;
        }
        $element[$index] = $this->addClass($item, ...$classes);
      }
      return $element;
    }
    $attributes = new Attribute($element['#attributes'] ?? []);
    $attributes->addClass(...$classes);
    $element['#attributes'] = $attributes->toArray();

    // Make sure element gets rendered again.
    unset($element['#printed']);

    return $element;
  }

  /**
   * Set attribute on a given element.
   *
   * @param mixed $element
   *   A render array.
   * @param string $name
   *   The attribute name.
   * @param mixed $value
   *   (optional) The attribute value.
   *
   * @return mixed
   *   The element with the given sanitized attribute's value or the unchanged
   *   element if passed value is not an array.
   *
   * @see Drupal\Core\Template\TwigExtension::setAttribute()
   */
  public function setAttribute(mixed $element, string $name, mixed $value = NULL): mixed {
    if (!\is_array($element)) {
      return $element;
    }
    if (\array_is_list($element)) {
      foreach ($element as $index => $item) {
        if (!\is_array($item)) {
          continue;
        }
        $element[$index] = $this->setAttribute($item, $name, $value);
      }
      return $element;
    }
    $element['#attributes'] = AttributeHelper::mergeCollections(
      $element['#attributes'] ?? [],
      new Attribute([$name => $value])
    );

    // Make sure element gets rendered again.
    unset($element['#printed']);

    return $element;
  }

}
