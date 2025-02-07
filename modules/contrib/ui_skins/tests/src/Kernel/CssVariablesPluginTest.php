<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_skins\Kernel;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the UI Skins CSS Variable plugin manager.
 *
 * @group ui_skins
 */
class CssVariablesPluginTest extends KernelTestBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'ui_skins',
    'ui_skins_test',
  ];

  /**
   * Tests that plugins can be provided by YAML files.
   */
  public function testDetectedPlugins(): void {
    /** @var \Drupal\ui_skins\CssVariable\CssVariablePluginManagerInterface $cssVariablePluginManager */
    $cssVariablePluginManager = $this->container->get('plugin.manager.ui_skins.css_variable');
    $definitions = $cssVariablePluginManager->getDefinitions();

    $this->assertEquals(1, \count($definitions), 'There is one variable detected.');

    $expectations = [
      'test' => [
        'id' => 'test',
        'provider' => 'ui_skins_test',
        'label' => $this->t('Test label module'),
        'description' => $this->t('Test plugin from module.'),
        'default_values' => [
          ':root' => 'Test value',
        ],
        'enabled' => TRUE,
      ],
    ];
    foreach ($expectations as $plugin_id => $expected_plugin_structure) {
      $definition_as_array = $definitions[$plugin_id]->toArray();
      foreach ($expected_plugin_structure as $key => $value) {
        $this->assertEquals($value, $definition_as_array[$key]);
      }
    }
  }

  /**
   * Test that it is possible to override an already declared plugin.
   */
  public function testOverridingDefinition(): void {
    $this->enableModules(['ui_skins_test_disabled']);

    // Test when the module overriding the definition is executed before.
    \module_set_weight('ui_skins_test_disabled', -1);
    /** @var \Drupal\ui_skins\CssVariable\CssVariablePluginManagerInterface $cssVariablePluginManager */
    $cssVariablePluginManager = $this->container->get('plugin.manager.ui_skins.css_variable');
    $this->assertArrayHasKey('test', $cssVariablePluginManager->getDefinitions());

    // Test when the module overriding the definition is executed after.
    \module_set_weight('ui_skins_test_disabled', 1);
    \drupal_flush_all_caches();
    /** @var \Drupal\ui_skins\CssVariable\CssVariablePluginManagerInterface $cssVariablePluginManager */
    $cssVariablePluginManager = $this->container->get('plugin.manager.ui_skins.css_variable');
    $this->assertArrayNotHasKey('test', $cssVariablePluginManager->getDefinitions());
  }

}
