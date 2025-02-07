<?php

declare(strict_types=1);

namespace Drupal\ui_patterns;

/**
 * Trait for plugins (sources and prop types) handling enum values.
 */
trait EnumTrait {

  use PropTypeConversionTrait;

  /**
   * Get form element options from enumeration.
   */
  protected static function getEnumOptions(array $definition): array {
    $values = array_combine(
      $definition['enum'],
      array_map(static function ($value) {
        return is_string($value) ? ucwords($value) : $value;
      }, $definition['enum']));
    if (!isset($definition['meta:enum'])) {
      return $values;
    }
    $meta = $definition['meta:enum'];
    // Remove meta:enum items not found in options.
    $meta = array_intersect_key($meta, $values);
    foreach ($meta as $value => $label) {
      $values[$value] = $label;
    }
    return $values;
  }

  /**
   * Get allowed values from enumeration.
   */
  protected static function getAllowedValues(array $definition): array {
    return array_keys(static::getEnumOptions($definition));
  }

  /**
   * Converts a source value type to enum data type.
   *
   * @param mixed $value
   *   The stored.
   * @param array $enum
   *   The defined enums.
   *
   * @return float|int|mixed
   *   The converted value.
   */
  protected static function convertValueToEnumType(mixed $value, array $enum) {
    if (!is_scalar($value)) {
      return $value;
    }
    return match (TRUE) {
      in_array($value, $enum, TRUE) => $value,
      in_array((string) $value, $enum, TRUE) => (string) $value,
      in_array((int) $value, $enum, TRUE)  => (int) $value,
      in_array((float) $value, $enum, TRUE) => (float) $value,
      default => $value,
    };
  }

  /**
   * Normalize the value without returning a default value.
   *
   * @param mixed $value
   *   The value to normalize.
   * @param array $enum
   *   The enum array.
   *
   * @return mixed
   *   The normalized value.
   */
  protected static function normalizeEnumValue(mixed $value, ?array $enum = NULL): mixed {
    if ($value !== NULL) {
      static::convertToScalar($value);
    }
    if (!is_array($enum) || empty($enum)) {
      return $value;
    }
    // We try to match first without casting.
    if (in_array($value, $enum, TRUE)) {
      return $value;
    }
    // We try to cast the value and retry to match.
    $value = static::convertValueToEnumType($value, $enum);
    if (in_array($value, $enum, TRUE)) {
      return $value;
    }
    return NULL;
  }

  /**
   * Get default value for an enum.
   *
   * @param array|null $definition
   *   The prop definition.
   *
   * @return mixed
   *   The default value.
   */
  protected static function enumDefaultValue(?array $definition = NULL): mixed {
    // First get the enum array.
    $enum = (!is_array($definition)) ? [] : ($definition['enum'] ?? []);
    if (!is_array($enum) || empty($enum)) {
      return NULL;
    }
    // Fall back to default value (if defined)
    if (isset($definition['default'])) {
      return $definition['default'];
    }
    // Return the first value, when
    // value is required.
    return (static::isEnumRequired($definition) && count($enum) > 0) ? $enum[0] : NULL;
  }

  /**
   * Check if the enum has a required value.
   *
   * @param array $definition
   *   The definition.
   *
   * @return bool
   *   Whether the enum has a required value.
   */
  protected static function isEnumRequired(array $definition): bool {
    $required_prop = $definition['required'] ?? FALSE;
    return $required_prop === TRUE;
  }

  /**
   * Normalize enum values.
   *
   * @param mixed $values
   *   The values to normalize.
   * @param array|null $enum
   *   The enum array.
   *
   * @return array
   *   The normalized values.
   */
  protected static function normalizeEnumValues(mixed $values, ?array $enum = NULL): array {
    if ($values === NULL) {
      return [];
    }
    if (!is_array($values)) {
      $values = [$values];
    }
    $values = array_map(function ($item) use ($enum) {
      return static::normalizeEnumValue($item, $enum);
    }, $values);
    $values = array_filter($values, function ($item) {
          return $item !== NULL;
    });
    return $values;
  }

  /**
   * Normalize enum list values.
   *
   * @param array $values
   *   The values to normalize.
   * @param array $definition
   *   The prop definition.
   * @param bool $uniqueItems
   *   Whether the items should be unique.
   *
   * @return array
   *   The normalized values.
   */
  protected static function normalizeEnumListSize(array $values, ?array $definition, bool $uniqueItems = FALSE): array {
    $definition_items = (!is_array($definition)) ? [] : ($definition['items'] ?? []);
    if (!is_array($definition_items) || empty($definition_items)) {
      return $values;
    }
    if (isset($definition['minItems']) && count($values) < (int) $definition['minItems']) {
      $default_value = static::enumDefaultValue($definition);
      $minItems = (int) $definition['minItems'];
      if (!$uniqueItems) {
        $values = array_merge($values, array_fill(0, $minItems - count($values), $default_value));
      }
      else {
        self::normalizeListMinSizeUniqueItems($values, $definition_items['enum'] ?? [], $default_value, $minItems);
      }
    }
    if (isset($definition['maxItems'])) {
      self::normalizeListMaxSize($values, (int) $definition['maxItems']);
    }
    return $values;
  }

  /**
   * Normalize list max size.
   */
  private static function normalizeListMaxSize(array &$values, int $maxItems): void {
    if (count($values) > $maxItems) {
      $values = array_slice($values, 0, $maxItems);
    }
  }

  /**
   * Normalize list min size unique items.
   */
  private static function normalizeListMinSizeUniqueItems(array &$values, mixed $possible_values, mixed $default_value, int $minItems): void {
    if (!is_array($possible_values)) {
      return;
    }
    // First try to add the default value.
    if (($default_value !== NULL) && !in_array($default_value, $values, TRUE)) {
      $values[] = $default_value;
    }
    $possible_values = array_diff($possible_values, $values);
    while ((count($possible_values) > 0) && count($values) < $minItems) {
      $values = array_unique(array_merge($values, [array_shift($possible_values)]));
    }
  }

}
