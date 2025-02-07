<?php

declare(strict_types=1);

namespace Drupal\ui_patterns_library;

/**
 * Stories syntax converter.
 *
 * Stories slots may skip the "#" prefix in render arrays for readability.
 * Let's put them back.
 *
 * Before: ["type" => "component", "component" => "example:card"]
 * After:  ["#type" => "component", "#component" => "example:card"]
 */
class StoriesSyntaxConverter {

  /**
   * An array with one (and only one) of those keys may be a render array.
   */
  const RENDER_KEYS = ["theme", "type", "markup", "plain_text", "#theme", "#type", "#markup", "#plain_text"];

  /**
   * Process stories slots.
   */
  public function convertSlots(array $slots): array {
    foreach ($slots as $slot_id => $slot) {
      if (!is_array($slot)) {
        continue;
      }
      $slots[$slot_id] = $this->convertArray($slot);
    }
    return $slots;
  }

  /**
   * Convert an array.
   */
  protected function convertArray(array $array): array {
    if ($this->isRenderArray($array)) {
      return $this->convertRenderArray($array);
    }
    foreach ($array as $index => $value) {
      if (!is_array($value)) {
        continue;
      }
      $array[$index] = $this->convertArray($value);
    }
    return $array;
  }

  /**
   * Convert a render array.
   */
  protected function convertRenderArray(array $renderable): array {
    foreach ($renderable as $property => $value) {
      if (is_array($value)) {
        $renderable[$property] = $this->convertArray($value);
      }
    }
    $in_html_tag = (isset($renderable["type"]) && $renderable["type"] === "html_tag");
    $html_tag_allowed_render_keys = ["type", "attributes", "tag", "value", "attached"];
    foreach ($renderable as $property => $value) {
      if (!is_string($property)) {
        continue;
      }
      // html_tag is special.
      if ($in_html_tag && !in_array($property, $html_tag_allowed_render_keys)) {
        continue;
      }
      if (str_starts_with($property, "#")) {
        continue;
      }
      $renderable["#" . $property] = $value;
      unset($renderable[$property]);
    }
    return $renderable;
  }

  /**
   * Is the array a render array?
   */
  protected function isRenderArray(array $array): bool {
    if (array_is_list($array)) {
      return FALSE;
    }
    // An array needs one, and only one, of those properties to be a render
    // array.
    $intersect = array_intersect(array_keys($array), self::RENDER_KEYS);
    if (count($intersect) != 1) {
      return FALSE;
    }
    // This property has to be a string value.
    $property = $intersect[array_key_first($intersect)];
    if (!is_string($array[$property])) {
      return FALSE;
    }
    return TRUE;
  }

}
