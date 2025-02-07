<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_skins\Unit;

use Drupal\Core\Form\FormState;
use Drupal\Tests\UnitTestCase;
use Drupal\ui_skins\Element\AlphaColor;

/**
 * @coversDefaultClass \Drupal\ui_skins\Element\AlphaColor
 *
 * @group ui_skins
 */
class AlphaColorElementTest extends UnitTestCase {

  /**
   * Test valueCallback.
   *
   * @param mixed $expected
   *   The expected return depending on the input.
   * @param mixed $input
   *   The input.
   *
   * @covers ::valueCallback
   *
   * @dataProvider providerTestValueCallback
   */
  public function testValueCallback($expected, $input): void {
    $element = [];
    $form_state = new FormState();
    $this->assertSame($expected, AlphaColor::valueCallback($element, $input, $form_state));
  }

  /**
   * Data provider for testValueCallback().
   */
  public static function providerTestValueCallback(): array {
    $data = [];
    $data[] = [NULL, FALSE];
    $data[] = [NULL, NULL];
    $data[] = [NULL, '#000'];
    $data[] = [NULL, '#000000'];
    $data[] = [NULL, '#00000000'];
    $data[] = ['#0d6efdff', [
      'color' => '#0d6efd',
      'alpha' => '255',
    ],
    ];
    $data[] = ['#0d6efd00', [
      'color' => '#0d6efd',
      'alpha' => '0',
    ],
    ];
    $data[] = ['#0d6efd00', [
      'color' => '#0d6efd',
      'alpha' => '00',
    ],
    ];
    $data[] = ['#0d6efd42', [
      'color' => '#0d6efd',
      'alpha' => '66',
    ],
    ];

    return $data;
  }

}
