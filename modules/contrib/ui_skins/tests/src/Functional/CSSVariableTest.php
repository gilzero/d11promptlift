<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_skins\Functional;

use Drupal\ui_skins\UiSkinsInterface;
use Drupal\ui_skins\UiSkinsUtility;

/**
 * CSS variables tests.
 *
 * @group ui_skins
 */
class CSSVariableTest extends UiSkinsFunctionalTestBase {

  /**
   * Test the rendered inline CSS.
   */
  public function testInlineCss(): void {
    $theme_settings = $this->configFactory->getEditable('ui_skins_test_subsubtheme.settings');

    // Test inline CSS values and scopes.
    $theme_settings->set(UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY, [
      'test' => [
        ':root' => 'Test value',
      ],
      'ui_skins_test_theme1' => [
        ':root' => 'Test value',
      ],
      'ui_skins_test_subtheme' => [
        '%my-subtheme-class' => 'Test value',
      ],
      'ui_skins_test_subsubtheme' => [
        '%my-subsubtheme-class' => 'overridden value',
      ],
    ]);
    $theme_settings->save();

    $this->drupalGet('<front>');

    $expected_css_variables = [
      ':root' => [
        '--test' => 'Test value',
        '--ui-skins-test-theme1' => 'Test value',
      ],
      '.my-subtheme-class' => [
        '--ui-skins-test-subtheme' => 'Test value',
      ],
      '.my-subsubtheme-class' => [
        '--ui-skins-test-subsubtheme' => 'overridden value',
      ],
    ];
    $this->assertSession()->pageTextContains(UiSkinsUtility::getCssVariablesInlineCss($expected_css_variables));

    // Test when there is no value for an existing plugin.
    $theme_settings->set(UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY, [
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
    $theme_settings->save();

    $this->drupalGet('<front>');

    $expected_css_variables = [
      ':root' => [
        '--test' => 'Test value',
        '--ui-skins-test-theme1' => 'Test value',
      ],
      '.my-subsubtheme-class' => [
        '--ui-skins-test-subsubtheme' => 'overridden value 2',
      ],
    ];
    $this->assertSession()->pageTextContains(UiSkinsUtility::getCssVariablesInlineCss($expected_css_variables));
    $this->assertSession()->pageTextNotContains('non existing');
  }

}
