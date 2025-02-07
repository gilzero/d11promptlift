<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_suite_bootstrap\Kernel;

use Drupal\KernelTests\Components\ComponentKernelTestBase;
use Drupal\sdc_devel\Controller\ComponentValidatorOverview;

/**
 * Validate components.
 *
 * @group ui_suite_bootstrap
 */
class ComponentValidatorTest extends ComponentKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'sdc_devel',
    'ui_icons',
    'ui_icons_backport',
    'ui_patterns',
    'ui_patterns_library',
    'ui_styles',
    'ui_styles_entity_status',
    'ui_styles_page',
    'layout_options',
  ];

  /**
   * {@inheritdoc}
   */
  protected static $themes = [
    'ui_suite_bootstrap',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig('system');
  }

  /**
   * Validate components.
   */
  public function testComponents(): void {
    $validatorController = new ComponentValidatorOverview(
      $this->container->get('sdc_devel.validator'),
      $this->container->get('plugin.manager.sdc'),
      $this->container->get('renderer')
    );
    $overview = $validatorController->overview();

    if (isset($overview['table']['#rows']) && !empty($overview['table']['#rows'])) {
      $messages = [];
      foreach ($overview['table']['#rows'] as $row) {
        $message = [
          $row['data'][0]->getText(),
          $row['data'][1],
          $row['data'][2],
          $row['data'][3],
          'Line ' . $row['data'][4],
        ];
        $messages[] = \implode(' | ', $message);
      }
      $this->fail(\implode(\PHP_EOL, $messages));
    }
    else {
      $this->assertTrue(TRUE, 'No errors found');
    }
  }

}
