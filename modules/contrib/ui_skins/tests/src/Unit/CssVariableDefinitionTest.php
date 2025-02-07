<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_skins\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\ui_skins\Definition\CssVariableDefinition;

/**
 * @coversDefaultClass \Drupal\ui_skins\Definition\CssVariableDefinition
 *
 * @group ui_skins
 */
class CssVariableDefinitionTest extends UnitTestCase {

  /**
   * Test getters.
   *
   * @param string $getter
   *   The getter callback.
   * @param string $name
   *   The name of the plugin attributes.
   * @param mixed $value
   *   The attribute's value.
   *
   * @covers ::getCategory
   * @covers ::getDefaultValues
   * @covers ::getDescription
   * @covers ::getLabel
   * @covers ::getProvider
   * @covers ::getType
   * @covers ::getWeight
   * @covers ::id
   * @covers ::isEnabled
   *
   * @dataProvider definitionGettersProvider
   */
  public function testGetters(string $getter, string $name, $value): void {
    $definition = new CssVariableDefinition([$name => $value]);
    // @phpstan-ignore-next-line
    $this->assertEquals(\call_user_func([$definition, $getter]), $value);
  }

  /**
   * Provider.
   *
   * @return array
   *   Data.
   */
  public static function definitionGettersProvider(): array {
    return [
      ['getProvider', 'provider', 'my_module'],
      ['id', 'id', 'plugin_id'],
      ['getLabel', 'label', 'Plugin label'],
      ['getDescription', 'description', 'Plugin description.'],
      ['getCategory', 'category', 'Plugin category'],
      ['getDefaultValues', 'default_values', ['my_scope' => 'my value']],
      ['getType', 'type', 'Plugin type'],
      ['getWeight', 'weight', 10],
      ['isEnabled', 'enabled', FALSE],
      ['isEnabled', 'enabled', TRUE],
    ];
  }

  /**
   * Test isDefaultScopeValue.
   *
   * @param string $scope
   *   The scope.
   * @param string $value
   *   The value.
   * @param bool $expected
   *   The expected result.
   *
   * @covers ::isDefaultScopeValue
   *
   * @dataProvider definitionDefaultScopeValueProvider
   */
  public function testIsDefaultScopeValue(string $scope, string $value, bool $expected): void {
    $definition = new CssVariableDefinition([
      'default_values' => [
        ':root' => 'test',
        'other_scope' => 'other value',
      ],
    ]);
    $this->assertEquals($expected, $definition->isDefaultScopeValue($scope, $value));
  }

  /**
   * Provider.
   *
   * @return array
   *   Data.
   */
  public static function definitionDefaultScopeValueProvider(): array {
    return [
      [':root', 'test', TRUE],
      [':root', 'non default value', FALSE],
      ['other_scope', 'other value', TRUE],
      ['other_scope', 'non default value', FALSE],
      ['non_existing_scope', 'test', FALSE],
      ['non_existing_scope', 'other value', FALSE],
    ];
  }

}
