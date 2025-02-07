<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_skins\Functional;

use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\ui_skins\UiSkinsInterface;

/**
 * Test uninstall ui_skins module.
 *
 * @group ui_skins
 */
class UiSkinsUninstallTest extends UiSkinsFunctionalTestBase {

  /**
   * The module installer service.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected ModuleInstallerInterface $moduleInstaller;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->moduleInstaller = $this->container->get('module_installer');
  }

  /**
   * Test function.
   */
  public function testUninstall(): void {
    $themeSettings = $this->configFactory->getEditable($this->defaultTheme . '.settings');
    $themeSettings->set(UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY, [
      'test' => [
        ':root' => 'Test value',
      ],
      'ui_skins_test_theme1' => [
        ':root' => 'Test value',
      ],
      'ui_skins_test_subsubtheme' => [
        '%my-subsubtheme-class' => 'overridden value 2',
      ],
    ]);
    $themeSettings->set(UiSkinsInterface::THEME_THEME_SETTING_KEY, 'mode1');
    $themeSettings->save();

    $css_variables_settings = $themeSettings->get(UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY);
    $this->assertNotNull($css_variables_settings);
    $theme_settings = $themeSettings->get(UiSkinsInterface::THEME_THEME_SETTING_KEY);
    $this->assertNotNull($theme_settings);

    $this->moduleInstaller->uninstall(['ui_skins']);

    $themeSettings = $this->configFactory->getEditable($this->defaultTheme . '.settings');
    $css_variables_settings = $themeSettings->get(UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY);
    $this->assertNull($css_variables_settings);
    $theme_settings = $themeSettings->get(UiSkinsInterface::THEME_THEME_SETTING_KEY);
    $this->assertNull($theme_settings);
  }

}
