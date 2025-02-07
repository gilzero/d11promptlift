<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_skins\Kernel;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the UI Skins theme plugin manager.
 *
 * @group ui_skins
 */
class ThemePluginTest extends KernelTestBase {

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
    /** @var \Drupal\ui_skins\Theme\ThemePluginManagerInterface $themePluginManager */
    $themePluginManager = $this->container->get('plugin.manager.ui_skins.theme');
    $definitions = $themePluginManager->getDefinitions();

    $this->assertEquals(1, \count($definitions), 'There is one theme detected.');
    $expectations = [
      'theme_from_module' => [
        'id' => 'theme_from_module',
        'label' => $this->t('Theme from module'),
        'description' => $this->t('Theme from module.'),
      ],
    ];
    foreach ($expectations as $plugin_id => $expected_plugin_structure) {
      $definition_as_array = $definitions[$plugin_id]->toArray();
      foreach ($expected_plugin_structure as $key => $value) {
        $this->assertEquals($value, $definition_as_array[$key]);
      }
    }
  }

}
