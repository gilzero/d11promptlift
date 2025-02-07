<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_skins\Functional;

use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\Url;
use Drupal\ui_skins\UiSkinsInterface;

/**
 * Theme mode tests.
 *
 * @group ui_skins
 */
class ThemeTest extends UiSkinsFunctionalTestBase {

  /**
   * List of themes to enable.
   *
   * @var array
   */
  protected array $themes = [
    'ui_skins_test_themes',
    'ui_skins_test_theme1',
    'ui_skins_test_theme2',
    'ui_skins_test_theme3',
    'ui_skins_test_subtheme',
    'ui_skins_test_subsubtheme',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'ui_skins_test_themes';

  /**
   * The theme extension list.
   *
   * @var \Drupal\Core\Extension\ThemeExtensionList
   */
  protected ThemeExtensionList $themeExtensionList;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->themeExtensionList = $this->container->get('extension.list.theme');
  }

  /**
   * Test theme settings form.
   *
   * Test that only modules, parent themes and theme CSS variables appear.
   */
  public function testPluginsDetectionOnThemeSettingsForm(): void {
    $this->drupalLogin($this->adminUser);

    $expected_results = [
      'ui_skins_test_theme3' => [
        'present' => [
          'theme_from_module' => 'Theme from module',
        ],
        'absent' => [
          'ui_skins_test_theme1' => 'Test theme 1',
          'ui_skins_test_theme2' => 'Test theme 2',
          'ui_skins_test_subtheme' => 'Test subtheme',
          'ui_skins_test_subsubtheme' => 'Test subsubtheme',
        ],
      ],
      'ui_skins_test_theme2' => [
        'present' => [
          'theme_from_module' => 'Theme from module',
          'ui_skins_test_theme2' => 'Test theme 2',
        ],
        'absent' => [
          'ui_skins_test_theme1' => 'Test theme 1',
          'ui_skins_test_subtheme' => 'Test subtheme',
          'ui_skins_test_subsubtheme' => 'Test subsubtheme',
        ],
      ],
      'ui_skins_test_theme1' => [
        'present' => [
          'theme_from_module' => 'Theme from module',
          'ui_skins_test_theme1' => 'Test theme 1',
        ],
        'absent' => [
          'ui_skins_test_theme2' => 'Test theme 2',
          'ui_skins_test_subtheme' => 'Test subtheme',
          'ui_skins_test_subsubtheme' => 'Test subsubtheme',
        ],
      ],
      'ui_skins_test_subtheme' => [
        'present' => [
          'theme_from_module' => 'Theme from module',
          'ui_skins_test_theme1' => 'Test theme 1',
          'ui_skins_test_subtheme' => 'Test subtheme',
        ],
        'absent' => [
          'ui_skins_test_theme2' => 'Test theme 2',
          'ui_skins_test_subsubtheme' => 'Test subsubtheme',
        ],
      ],
      'ui_skins_test_subsubtheme' => [
        'present' => [
          'theme_from_module' => 'Theme from module',
          'ui_skins_test_theme1' => 'Test theme 1',
          'ui_skins_test_subtheme' => 'Test subtheme',
          'ui_skins_test_subsubtheme' => 'Test subsubtheme',
        ],
        'absent' => [
          'ui_skins_test_theme2' => 'Test theme 2',
        ],
      ],
    ];

    foreach ($expected_results as $theme => $form_infos) {
      $this->drupalGet(Url::fromRoute('system.theme_settings_theme', [
        'theme' => $theme,
      ]));

      $this->assertSession()->elementExists('css', '#edit-ui-skins-theme');

      foreach ($form_infos['present'] as $option_key => $option_label) {
        $this->assertEquals($option_label, $this->assertSession()->optionExists('ui_skins_theme', $option_key)->getText());
      }

      foreach ($form_infos['absent'] as $option_key => $option_label) {
        $this->assertSession()->optionNotExists('ui_skins_theme', $option_key);
      }
    }
  }

  /**
   * Test config state before saving.
   */
  public function testThemeBeforeThemeSettingsSubmit(): void {
    $theme_settings = $this->config('ui_skins_test_subsubtheme.settings');
    $ui_skins_theme_mode = $theme_settings->get(UiSkinsInterface::THEME_THEME_SETTING_KEY);
    $this->assertNull($ui_skins_theme_mode);
  }

  /**
   * Test config state after save.
   */
  public function testThemeThemeSettingsSubmit(): void {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet(Url::fromRoute('system.theme_settings_theme', [
      'theme' => 'ui_skins_test_subsubtheme',
    ]));
    $this->submitForm([
      'ui_skins_theme' => 'ui_skins_test_subsubtheme',
    ], 'Save configuration');

    $config = $this->config('ui_skins_test_subsubtheme.settings');
    $ui_skins_theme_mode = $config->get(UiSkinsInterface::THEME_THEME_SETTING_KEY);
    $this->assertEquals('ui_skins_test_subsubtheme', $ui_skins_theme_mode);
  }

  /**
   * Test if attributes are set correctly.
   */
  public function testHtmlResult(): void {
    $ui_skins_test_themes_path = $this->themeExtensionList->getPath('ui_skins_test_themes');

    $this->setTheme('test_1');
    $this->drupalGet(Url::fromRoute('<front>'));
    $this->assertSession()->elementExists('css', 'html[data-test="test_1"]');

    \drupal_flush_all_caches();
    $this->setTheme('test_2');
    $this->drupalGet(Url::fromRoute('<front>'));
    $this->assertSession()->elementExists('css', 'html.test-2');

    \drupal_flush_all_caches();
    $this->setTheme('test_3');
    $this->drupalGet(Url::fromRoute('<front>'));
    $this->assertSession()->elementExists('css', 'body[data-test="test_3"]');

    \drupal_flush_all_caches();
    $this->setTheme('test_4');
    $this->drupalGet(Url::fromRoute('<front>'));
    $this->assertSession()->elementExists('css', 'body.test-4');

    \drupal_flush_all_caches();
    $this->setTheme('test_5');
    $this->drupalGet(Url::fromRoute('<front>'));
    $this->assertSession()->elementExists('css', 'body[data-test="test_5"]');

    \drupal_flush_all_caches();
    $this->setTheme('test_6');
    $this->drupalGet(Url::fromRoute('<front>'));
    $this->assertSession()->elementExists('css', 'body.test-6');

    \drupal_flush_all_caches();
    $this->setTheme('test_7');
    $this->drupalGet(Url::fromRoute('<front>'));
    $this->assertSession()->elementExists('css', 'body.test-7');
    $this->assertSession()->responseContains('href="' . Url::fromUserInput('/' . $ui_skins_test_themes_path . '/assets/css/test.css')->toString());

    \drupal_flush_all_caches();
    $this->setTheme('test_8');
    $this->drupalGet(Url::fromRoute('<front>'));
    $this->assertSession()->elementExists('css', 'body.test-7.test-8');
    $this->assertSession()->responseContains('href="' . Url::fromUserInput('/' . $ui_skins_test_themes_path . '/assets/css/test.css')->toString());
    $this->assertSession()->responseContains('href="' . Url::fromUserInput('/' . $ui_skins_test_themes_path . '/assets/css/test_8.css')->toString());
  }

  /**
   * Change theme settings.
   */
  protected function setTheme(string $plugin_id): void {
    $config = $this->config($this->defaultTheme . '.settings');
    $config->set(UiSkinsInterface::THEME_THEME_SETTING_KEY, $plugin_id)
      ->save();
  }

}
