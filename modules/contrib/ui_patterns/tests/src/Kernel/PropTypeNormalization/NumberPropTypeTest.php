<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_patterns\Kernel\PropTypeNormalization;

use Drupal\Tests\ui_patterns\Kernel\PropTypeNormalizationTestBase;
use Drupal\ui_patterns\Plugin\UiPatterns\PropType\NumberPropType;

/**
 * Test NumberPropType normalization.
 *
 * @coversDefaultClass \Drupal\ui_patterns\Plugin\UiPatterns\PropType\NumberPropType
 * @group ui_patterns
 */
class NumberPropTypeTest extends PropTypeNormalizationTestBase {

  /**
   * Test normalize static method with prop number.
   *
   * @dataProvider normalizationTests
   */
  public function testNormalization(mixed $value, mixed $expected) : void {
    $normalized = NumberPropType::normalize($value, $this->testComponentProps['number']);
    $this->assertEquals($normalized, $expected);
  }

  /**
   * Test normalize static method with prop integer.
   *
   * @dataProvider normalizationTestsInteger
   */
  public function testNormalizationInteger(mixed $value, mixed $expected) : void {
    $normalized = NumberPropType::normalize($value, $this->testComponentProps['integer']);
    $this->assertEquals($normalized, $expected);
  }

  /**
   * Test rendered component with prop.
   *
   * @dataProvider renderingTests
   */
  public function testRendering(mixed $value, mixed $rendered_value) : void {
    $this->runRenderPropTest('number', ["value" => $value, "rendered_value" => $rendered_value]);
  }

  /**
   * Provides data for testNormalization.
   */
  public static function normalizationTests() : array {
    return [
      "null value" => [NULL, NULL],
      "integer value" => [1, 1],
      "float value" => [1.1, 1.1],
      "string value" => ["1", 1],
      "float string value" => ["1.1", 1.1],
    ];
  }

  /**
   * Provides data for testNormalizationInteger.
   */
  public static function normalizationTestsInteger() : array {
    return [
      "null value" => [NULL, NULL],
      "integer value to integer" => [1, 1],
      "float value to integer" => [1.1, 1],
      "string value to integer" => ["1", 1],
      "float string value to integer" => ["1.1", 1],
    ];
  }

  /**
   * Provides data for testNormalization.
   */
  public static function renderingTests() : array {
    return [
      "null value" => [
        NULL,
        '<div class="ui-patterns-props-number"></div>',
      ],
    ];
  }

}
