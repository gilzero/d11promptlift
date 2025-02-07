<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_skins\Functional;

use Drupal\Core\Url;

/**
 * Alpha color form tests.
 *
 * @group ui_skins
 */
class AlphaColorFormTest extends UiSkinsFunctionalTestBase {

  /**
   * Test that value is correctly processed in alpha color element.
   */
  public function testAlphaColorForm(): void {
    $assert = $this->assertSession();
    $this->drupalGet(Url::fromRoute('ui_skins_test.alpha_color'));

    // Check default values.
    $assert->fieldValueEquals('alpha_color[color]', '#0d6efd');
    $assert->fieldValueEquals('alpha_color[alpha]', '255');

    $edit = [
      'alpha_color[color]' => '#000000',
      'alpha_color[alpha]' => '66',
    ];
    $this->submitForm($edit, 'Submit');
    $assert->statusCodeEquals(200);
    $assert->pageTextContains('You specified a color of #00000042.');
  }

}
