<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_skins\Functional;

use Drupal\Core\Url;
use Drupal\ui_skins\UiSkinsInterface;

/**
 * Theme settings form tests.
 *
 * @group ui_skins
 */
class ThemeSettingsFormTest extends UiSkinsFunctionalTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'claro';

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
          'edit-ui-skins-css-variables-test' => [
            'label' => 'Test label module',
            'css_variable' => '--test',
          ],
        ],
        'absent' => [
          'edit-ui-skins-css-variables-ui-skins-test-theme1' => [
            'label' => 'Test theme 1',
            'css_variable' => '--ui-skins-test-theme1',
          ],
          'edit-ui-skins-css-variables-ui-skins-test-theme2' => [
            'label' => 'Test theme 2',
            'css_variable' => '--ui-skins-test-theme2',
          ],
          'edit-ui-skins-css-variables-ui-skins-test-subtheme' => [
            'label' => 'Test subtheme',
            'css_variable' => '--ui-skins-test-subtheme',
          ],
          'edit-ui-skins-css-variables-subsubtheme-group-ui-skins-test-subsubtheme' => [
            'label' => 'Test subsubtheme',
            'css_variable' => '--ui-skins-test-subsubtheme',
          ],
        ],
      ],
      'ui_skins_test_theme2' => [
        'present' => [
          'edit-ui-skins-css-variables-test' => [
            'label' => 'Test label module',
            'css_variable' => '--test',
          ],
          'edit-ui-skins-css-variables-ui-skins-test-theme2' => [
            'label' => 'Test theme 2',
            'css_variable' => '--ui-skins-test-theme2',
          ],
        ],
        'absent' => [
          'edit-ui-skins-css-variables-ui-skins-test-theme1' => [
            'label' => 'Test theme 1',
            'css_variable' => '--ui-skins-test-theme1',
          ],
          'edit-ui-skins-css-variables-ui-skins-test-subtheme' => [
            'label' => 'Test subtheme',
            'css_variable' => '--ui-skins-test-subtheme',
          ],
          'edit-ui-skins-css-variables-subsubtheme-group-ui-skins-test-subsubtheme' => [
            'label' => 'Test subsubtheme',
            'css_variable' => '--ui-skins-test-subsubtheme',
          ],
        ],
      ],
      'ui_skins_test_theme1' => [
        'present' => [
          'edit-ui-skins-css-variables-test' => [
            'label' => 'Test label module',
            'css_variable' => '--test',
          ],
          'edit-ui-skins-css-variables-ui-skins-test-theme1' => [
            'label' => 'Test theme 1',
            'css_variable' => '--ui-skins-test-theme1',
          ],
        ],
        'absent' => [
          'edit-ui-skins-css-variables-ui-skins-test-theme2' => [
            'label' => 'Test theme 2',
            'css_variable' => '--ui-skins-test-theme2',
          ],
          'edit-ui-skins-css-variables-ui-skins-test-subtheme' => [
            'label' => 'Test subtheme',
            'css_variable' => '--ui-skins-test-subtheme',
          ],
          'edit-ui-skins-css-variables-subsubtheme-group-ui-skins-test-subsubtheme' => [
            'label' => 'Test subsubtheme',
            'css_variable' => '--ui-skins-test-subsubtheme',
          ],
        ],
      ],
      'ui_skins_test_subtheme' => [
        'present' => [
          'edit-ui-skins-css-variables-test' => [
            'label' => 'Test label module',
            'css_variable' => '--test',
          ],
          'edit-ui-skins-css-variables-ui-skins-test-theme1' => [
            'label' => 'Test theme 1',
            'css_variable' => '--ui-skins-test-theme1',
          ],
          'edit-ui-skins-css-variables-ui-skins-test-subtheme' => [
            'label' => 'Test subtheme',
            'css_variable' => '--ui-skins-test-subtheme',
          ],
        ],
        'absent' => [
          'edit-ui-skins-css-variables-ui-skins-test-theme2' => [
            'label' => 'Test theme 2',
            'css_variable' => '--ui-skins-test-theme2',
          ],
          'edit-ui-skins-css-variables-subsubtheme-group-ui-skins-test-subsubtheme' => [
            'label' => 'Test subsubtheme',
            'css_variable' => '--ui-skins-test-subsubtheme',
          ],
        ],
      ],
      'ui_skins_test_subsubtheme' => [
        'present' => [
          'edit-ui-skins-css-variables-other-test' => [
            'label' => 'Test label module',
            'css_variable' => '--test',
          ],
          'edit-ui-skins-css-variables-other-ui-skins-test-theme1' => [
            'label' => 'Test theme 1',
            'css_variable' => '--ui-skins-test-theme1',
          ],
          'edit-ui-skins-css-variables-other-ui-skins-test-subtheme' => [
            'label' => 'Test subtheme',
            'css_variable' => '--ui-skins-test-subtheme',
          ],
          'edit-ui-skins-css-variables-subsubtheme-group-ui-skins-test-subsubtheme' => [
            'label' => 'Test subsubtheme',
            'css_variable' => '--ui-skins-test-subsubtheme',
          ],
        ],
        'absent' => [
          'edit-ui-skins-css-variables-ui-skins-test-theme2' => [
            'label' => 'Test theme 2',
            'css_variable' => '--ui-skins-test-theme2',
          ],
        ],
      ],
    ];

    foreach ($expected_results as $theme => $form_infos) {
      $this->drupalGet(Url::fromRoute(self::CONFIG_ROUTE_NAME, [
        'theme' => $theme,
      ]));

      foreach ($form_infos['present'] as $form_element_id => $form_elements) {
        $this->assertSession()->elementExists('css', '#' . $form_element_id);
        $this->assertSession()->pageTextContains($form_elements['label']);
        $this->assertSession()->pageTextContains('Variable: ' . $form_elements['css_variable']);
      }

      foreach ($form_infos['absent'] as $form_element_id => $form_elements) {
        $this->assertSession()->elementNotExists('css', '#' . $form_element_id);
        $this->assertSession()->pageTextNotContains($form_elements['label']);
        $this->assertSession()->pageTextNotContains('Variable: ' . $form_elements['css_variable']);
      }
    }
  }

  /**
   * Test that config is flattened when saved.
   */
  public function testThemeSettingsSubmitState0(): void {
    $theme_settings = $this->config('ui_skins_test_subsubtheme.settings');
    $ui_skins_css_variables = $theme_settings->get(UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY);
    $this->assertNull($ui_skins_css_variables);
  }

  /**
   * Test that config is flattened when saved.
   */
  public function testThemeSettingsSubmitState1(): void {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet(Url::fromRoute(self::CONFIG_ROUTE_NAME, [
      'theme' => 'ui_skins_test_subsubtheme',
    ]));
    $this->submitForm([
      'ui_skins_css_variables[subsubtheme_group][ui_skins_test_subsubtheme][values_container][0][value]' => 'overridden value',
    ], 'Save configuration');

    $theme_settings = $this->config('ui_skins_test_subsubtheme.settings');
    $ui_skins_css_variables = $theme_settings->get(UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY);
    $this->assertIsArray($ui_skins_css_variables);

    $this->assertEquals([
      'ui_skins_test_subsubtheme' => [
        '%my-subsubtheme-class' => 'overridden value',
      ],
    ], $ui_skins_css_variables);
  }

  /**
   * Test that config is flattened when saved.
   */
  public function testThemeSettingsSubmitState2(): void {
    $this->drupalLogin($this->adminUser);

    // Try saving a 0 value.
    $this->drupalGet(Url::fromRoute(self::CONFIG_ROUTE_NAME, [
      'theme' => 'ui_skins_test_subsubtheme',
    ]));
    $this->submitForm([
      'ui_skins_css_variables[subsubtheme_group][ui_skins_test_subsubtheme][values_container][0][value]' => '0',
    ], 'Save configuration');

    $theme_settings = $this->config('ui_skins_test_subsubtheme.settings');
    $ui_skins_css_variables = $theme_settings->get(UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY);
    $this->assertIsArray($ui_skins_css_variables);

    $this->assertEquals([
      'ui_skins_test_subsubtheme' => [
        '%my-subsubtheme-class' => '0',
      ],
    ], $ui_skins_css_variables);
  }

}
