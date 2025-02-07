<?php

namespace Drupal\Tests\typogrify\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\typogrify\Typogrify;

/**
 * Testing all methods on the Typogrify class and their interaction.
 *
 * @group typogrify
 */
class TypogrifyClassTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['typogrify'];

  /**
   * Tests ampersand-wrapping.
   */
  public function testAmpersandWrapping() {
    $before = 'John & Robert.';
    $after = 'John <span class="amp">&amp;</span> Robert.';
    $this->assertSame(Typogrify::amp($before), $after,
      'Basic ampersand wrapping');

    $before = 'P&T';
    $after = 'P&T';
    $this->assertSame(Typogrify::amp($before), $after,
      "Don't mess with ampersands in words");

    $before = 'advanced robotics &&nbsp;computing...';
    $after = 'advanced robotics <span class="amp">&amp;</span>&nbsp;computing...';
    $this->assertSame(Typogrify::amp($before), $after,
      'One space as HTML entity.');

    $before = 'advanced robotics &amp; computing...';
    $after = 'advanced robotics <span class="amp">&amp;</span> computing...';
    $this->assertSame(Typogrify::amp($before), $after,
      'Ampersand as HTML entity.');

    $before = 'advanced robotics&nbsp;&amp;&nbsp;computing...';
    $after = 'advanced robotics&nbsp;<span class="amp">&amp;</span>&nbsp;computing...';
    $this->assertSame(Typogrify::amp($before), $after,
      'Both spaces and ampersand as HTML entities.');

    $before = 'P&amp;T had many clients, of which DD&T &amp; Cronhammar, Kronhammer & Hjort were the largest';
    $after = 'P&amp;T had many clients, of which DD&T <span class="amp">&amp;</span> Cronhammar, Kronhammer <span class="amp">&amp;</span> Hjort were the largest';
    $this->assertSame(Typogrify::amp($before), $after,
      'Both spaces and ampersand as HTML entities.');

  }

  /**
   * Widont test.
   */
  public function testWidont() {
    $before = 'A very simple test';
    $after = 'A very simple&nbsp;test';
    $this->assertSame(Typogrify::widont($before), $after,
      'Basic widont. Once sentence, no HTML.');

    // Single word items shouldn't be changed.
    $before = 'Test';
    $after = 'Test';
    $this->assertSame(Typogrify::widont($before), $after,
      'Single word test #1');

    $before = ' Test';
    $after = ' Test';
    $this->assertSame(Typogrify::widont($before), $after,
      'Single word test #2');

    $before = '<ul><li>Test</p></li><ul>';
    $after = '<ul><li>Test</p></li><ul>';
    $this->assertSame(Typogrify::widont($before), $after,
      'Single word test #3');

    $before = '<ul><li> Test</p></li><ul>';
    $after = '<ul><li> Test</p></li><ul>';
    $this->assertSame(Typogrify::widont($before), $after,
      'Single word test #4');

    $before = '<p>In a couple of paragraphs</p><p>paragraph two</p>';
    $after = '<p>In a couple of&nbsp;paragraphs</p><p>paragraph&nbsp;two</p>';
    $this->assertSame(Typogrify::widont($before), $after,
      'Two paragraphs');

    $before = '<h1><a href="#">In a link inside a heading</i> </a></h1>';
    $after = '<h1><a href="#">In a link inside a&nbsp;heading</i> </a></h1>';
    $this->assertSame(Typogrify::widont($before), $after,
      'In a link inside a heading');

    $before = '<h1><a href="#">In a link</a> followed by other text</h1>';
    $after = '<h1><a href="#">In a link</a> followed by other&nbsp;text</h1>';
    $this->assertSame(Typogrify::widont($before), $after,
      'In a link followed by other text');

    $before = '<h1><a href="#"></a></h1>';
    $after = '<h1><a href="#"></a></h1>';
    $this->assertSame(Typogrify::widont($before), $after,
      "Empty HTML tags shouldn't cause errors");

    $before = '<div>Divs get no love!</div>';
    $after = '<div>Divs get no love!</div>';
    $this->assertSame(Typogrify::widont($before), $after,
      'Divs get no love');

    $before = '<pre>Neither do PREs</pre>';
    $after = '<pre>Neither do PREs</pre>';
    $this->assertSame(Typogrify::widont($before), $after,
      'Neither do PREs');

    $before = '<div><p>But divs with paragraphs do!</p></div>';
    $after = '<div><p>But divs with paragraphs&nbsp;do!</p></div>';
    $this->assertSame(Typogrify::widont($before), $after,
      'But divs with paragraphs do!');
  }

}
