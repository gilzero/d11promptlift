<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_icons\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\ui_icons\Controller\IconAutocompleteController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \Drupal\ui_icons\Controller\IconAutocompleteController
 *
 * @group icon
 */
class IconAutocompleteControllerKernelTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'ui_icons',
    'ui_icons_test',
  ];

  /**
   * The IconAutocompleteController instance.
   *
   * @var \Drupal\ui_icons\Controller\IconAutocompleteController
   */
  private IconAutocompleteController $iconAutocompleteController;

  /**
   * The App root instance.
   *
   * @var string
   */
  private string $appRoot;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $iconSearch = $this->container->get('ui_icons.search');

    $this->iconAutocompleteController = new IconAutocompleteController(
      $iconSearch,
    );
  }

  /**
   * Tests the handleSearchIcons method of the IconAutocompleteController.
   */
  public function testHandleSearchIconsResultLabel(): void {
    $icon_full_id = 'test_minimal:foo';
    $search = $this->iconAutocompleteController->handleSearchIcons(new Request(['q' => $icon_full_id]));
    $result = json_decode($search->getContent(), TRUE);

    // Load the response to test, cannot simply compare string as `src` path is
    // based on physical path than can be specific for example in CI.
    $result_dom = new \DOMDocument();
    $result_dom->loadHTML($result[0]['label']);

    $this->assertSame('Foo (test_minimal)', trim($result_dom->lastChild->textContent));

    $result_xpath = new \DOMXpath($result_dom);

    $span = $result_xpath->query("//span");
    $this->assertSame('ui-menu-icon', $span->item(0)->getAttribute('class'));

    $img = $result_xpath->query("//span/img");
    $this->assertSame('icon icon-preview', $img->item(0)->getAttribute('class'));
    $this->assertSame($icon_full_id, $img->item(0)->getAttribute('title'));
    $this->assertSame('24', $img->item(0)->getAttribute('width'));
    $this->assertSame('24', $img->item(0)->getAttribute('height'));

    $src = $img->item(0)->getAttribute('src');
    $this->assertStringEndsWith('tests/modules/ui_icons_test/icons/flat/foo.png', $src);
  }

}
