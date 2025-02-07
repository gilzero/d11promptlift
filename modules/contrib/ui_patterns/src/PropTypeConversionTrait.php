<?php

declare(strict_types=1);

namespace Drupal\ui_patterns;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\RenderableInterface;

/**
 * Trait for plugins (prop types) handling conversion.
 */
trait PropTypeConversionTrait {

  /**
   * Try to downcast an ufo to a scalar value.
   *
   * @param mixed $value
   *   The value to convert.
   * @param bool $strip_tags_from_render_arrays
   *   Whether to strip tags from render arrays.
   */
  protected static function convertToScalar(mixed &$value, bool $strip_tags_from_render_arrays = TRUE) : void {
    if ($value instanceof RenderableInterface) {
      $value = $value->toRenderable();
    }
    elseif (($value instanceof MarkupInterface) || ($value instanceof \Stringable)) {
      $value = (string) $value;
    }
    elseif (is_object($value) && method_exists($value, 'toString')) {
      $value = $value->toString();
    }
    if (is_array($value)) {
      $value = static::convertArrayToScalar($value, $strip_tags_from_render_arrays);
    }
  }

  /**
   * Convert an array to scalar.
   *
   * @param array $array
   *   The array to convert.
   * @param bool $strip_tags_from_render_arrays
   *   Whether to strip tags from render arrays.
   *
   * @return mixed
   *   The converted array.
   */
  protected static function convertArrayToScalar(array $array, bool $strip_tags_from_render_arrays = TRUE) : mixed {
    if (empty($array)) {
      return NULL;
    }
    if (!empty(Element::properties($array))) {
      /** @var \Drupal\Core\Render\Renderer $renderer */
      $renderer = \Drupal::service('renderer');
      $value = (string) $renderer->renderInIsolation($array);
      if ($strip_tags_from_render_arrays) {
        $value = strip_tags($value);
      }
      return $value;
    }
    foreach ($array as $value) {
      if ($value === NULL) {
        continue;
      }
      static::convertToScalar($value, $strip_tags_from_render_arrays);
      return $value;
    }
    return NULL;
  }

  /**
   * Convert a value to a string.
   *
   * @param mixed $value
   *   The value to convert.
   *
   * @return string
   *   The converted value.
   */
  protected static function convertToString(mixed $value) : string {
    if ($value === NULL) {
      return '';
    }
    static::convertToScalar($value, FALSE);
    if (is_array($value)) {
      return json_encode($value, 0, 3) ?: "";
    }
    return is_string($value) ? $value : (string) $value;
  }

}
