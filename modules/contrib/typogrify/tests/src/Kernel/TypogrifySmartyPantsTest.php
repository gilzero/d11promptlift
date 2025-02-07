<?php

namespace Drupal\Tests\typogrify\Kernel;

use Drupal\filter\FilterPluginCollection;
use Drupal\KernelTests\KernelTestBase;

/**
 * Test the application of the full package of Typogrify and SmartyPants.
 *
 * @group typogrify
 */
class TypogrifySmartyPantsTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['typogrify', 'filter'];

  /**
   * Base settings.
   *
   * @var array
   */
  protected $baseSettings = [
    'smartypants_enabled' => 1,
    'smartypants_hyphens' => 2,
    'wrap_ampersand' => 1,
    'widont_enabled' => 1,
    'wrap_abbr' => 0,
    'wrap_caps' => 1,
    'wrap_initial_quotes' => 1,
    'hyphenate_shy' => 0,
    'wrap_numbers' => 0,
    'ligatures' => [],
    'arrows' => [],
    'quotes' => [],
  ];

  /**
   * Tests the filter, along with the Typogrify and SmartyPants libraries.
   *
   * @dataProvider providerTypogrifyExamples
   */
  public function testTypogrify(array $extra_settings, string $original, string $processed) {
    $configuration = ['settings' => $extra_settings + $this->baseSettings];
    $manager = $this->container->get('plugin.manager.filter');
    $bag = new FilterPluginCollection($manager, []);
    $filter = $bag->get('typogrify');
    $filter->setConfiguration($configuration);
    $this->assertEquals($processed, $filter->process($original, 'en'));
  }

  /**
   * Provides test data for ::testTypogrify().
   */
  public function providerTypogrifyExamples() {
    $before = <<<HTML
<h2>"Jayhawks" & KU fans act extremely obnoxiously</h2>
<p>By J.D. Salinger, Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. "Excepteur sint occaecat 'cupidatat' non proident" sunt RFID22 in.... </p>
HTML;
    $after = <<<HTML
<h2>“Jayhawks” <span class="amp">&amp;</span> <span class="caps">KU</span> fans act extremely&nbsp;obnoxiously</h2>
<p>By <span class="caps">J.D.</span> Salinger, Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. “Excepteur sint occaecat ‘cupidatat’ non proident” sunt <span class="caps">RFID22</span>&nbsp;in&#8230;. </p>
HTML;

    // Original example compatibility test.
    $data = [
      'original typogrify example' => [
        [],
        $before,
        $after,
      ],
      'test wrap_abbr' => [
        ['wrap_abbr' => 3],
        "What kind of abbreviations contain two dots like eg.etc. or cd.e.f.?",
        'What kind of abbreviations contain two dots like <span class="abbr">eg.<span style="margin-left:0.167em"><span style="display:none">&nbsp;</span></span>etc.</span> or <span class="abbr">cd.<span style="margin-left:0.167em"><span style="display:none">&nbsp;</span></span>e.<span style="margin-left:0.167em"><span style="display:none">&nbsp;</span></span>f.</span>?',
      ],
      'test wrap_numbers' => [
        ['wrap_numbers' => 4],
        "Mathematicians refer to 1729 as Ramanujan's number. Euler proved that e^(π i) = -1, where π is approximately 3.1415926.",
        'Mathematicians refer to <span class="number">1729</span> as Ramanujan&#8217;s number. Euler proved that e^(π i) = <span class="number">-1</span>, where π is approximately <span class="number">3.1415926</span>.',
      ],
    ];
    return $data;
  }

}
