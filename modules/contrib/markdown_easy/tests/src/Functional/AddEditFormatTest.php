<?php

declare(strict_types=1);

namespace Drupal\Tests\markdown_easy\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test Markdown Easy warnings when adding or editing a text format.
 *
 * @group markdown_easy
 */
class AddEditFormatTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'markdown_easy',
    'node',
    'field',
    'filter',
    'text',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalLogin($this->rootUser);
  }

  /**
   * Test various warning messages on format page when using Markdown Easy.
   *
   * @test
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testAddEditFormat(): void {
    // Start the browsing session.
    $session = $this->assertSession();

    // Navigate to the add text format page.
    $this->drupalGet('/admin/config/content/formats/add');
    $session->statusCodeEquals(200);

    // Populate the new text format form (tests "add" mode).
    $edit = [
      'name' => 'Markdown Easy format page test',
      'format' => 'markdown_easy_format_page_test',
      'filters[markdown_easy][status]' => 1,
      'filters[markdown_easy][settings][flavor]' => 'standard',
    ];
    $this->submitForm($edit, 'Save configuration');
    $session->statusCodeEquals(200);
    $session->pageTextContainsOnce('The <em class="placeholder">Markdown Easy</em> filter needs to be placed before the following filters: <em class="placeholder">Limit allowed HTML tags and correct faulty HTML, Convert line breaks into HTML');

    // Resubmit the text format with additional filters enabled, but in a
    // potentially insecure order.
    $edit = [
      'name' => 'Markdown Easy format page test',
      'format' => 'markdown_easy_format_page_test',
      'filters[markdown_easy][status]' => 1,
      'filters[markdown_easy][weight]' => 30,
      'filters[markdown_easy][settings][flavor]' => 'standard',
      'filters[filter_html][status]' => 1,
      'filters[filter_html][weight]' => 20,
      'filters[filter_autop][status]' => 1,
      'filters[filter_autop][weight]' => 10,
    ];
    $this->submitForm($edit, 'Save configuration');
    $session->statusCodeEquals(200);
    $session->pageTextContainsOnce('The <em class="placeholder">Markdown Easy</em> filter needs to be placed before the following filters: <em class="placeholder">Limit allowed HTML tags and correct faulty HTML, Convert line breaks into HTML');

    // Resubmit the text format with filters in a proper order.
    $edit = [
      'name' => 'Markdown Easy format page test',
      'format' => 'markdown_easy_format_page_test',
      'filters[markdown_easy][status]' => 1,
      'filters[markdown_easy][weight]' => 10,
      'filters[markdown_easy][settings][flavor]' => 'standard',
      'filters[filter_html][status]' => 1,
      'filters[filter_html][weight]' => 20,
      'filters[filter_autop][status]' => 1,
      'filters[filter_autop][weight]' => 30,
    ];
    $this->submitForm($edit, 'Save configuration');
    $session->statusCodeEquals(200);
    $session->pageTextContainsOnce('Added text format Markdown Easy format page test.');

    // Navigate back to the edit text format page (tests "edit" mode).
    $this->drupalGet('/admin/config/content/formats/manage/markdown_easy_format_page_test');
    $session->statusCodeEquals(200);
    $this->submitForm($edit, 'Save configuration');
    $session->statusCodeEquals(200);
    $session->pageTextContainsOnce('The text format Markdown Easy format page test has been updated.');

    // Resubmit the text format with additional filters enabled, but in a
    // potentially insecure order.
    $edit = [
      'name' => 'Markdown Easy format page test',
      'format' => 'markdown_easy_format_page_test',
      'filters[markdown_easy][status]' => 1,
      'filters[markdown_easy][weight]' => 30,
      'filters[markdown_easy][settings][flavor]' => 'standard',
      'filters[filter_html][status]' => 1,
      'filters[filter_html][weight]' => 20,
      'filters[filter_autop][status]' => 1,
      'filters[filter_autop][weight]' => 10,
    ];
    $this->submitForm($edit, 'Save configuration');
    $session->statusCodeEquals(200);
    $session->pageTextContainsOnce('The <em class="placeholder">Markdown Easy</em> filter needs to be placed before the following filters: <em class="placeholder">Limit allowed HTML tags and correct faulty HTML, Convert line breaks into HTML');

    // Resubmit the text format with "Limit HTML" and "Convert line breaks" in
    // the wrong order.
    $edit = [
      'name' => 'Markdown Easy format page test',
      'format' => 'markdown_easy_format_page_test',
      'filters[markdown_easy][status]' => 1,
      'filters[markdown_easy][weight]' => 0,
      'filters[markdown_easy][settings][flavor]' => 'standard',
      'filters[filter_html][status]' => 1,
      'filters[filter_html][weight]' => 20,
      'filters[filter_autop][status]' => 1,
      'filters[filter_autop][weight]' => 10,
    ];
    $this->submitForm($edit, 'Save configuration');
    $session->statusCodeEquals(200);
    $session->pageTextContainsOnce('The "Convert line breaks into HTML (i.e. &lt;br&gt; and &lt;p&gt;)" filter must run after the "Limit allowed HTML tags and correct faulty HTML" filter in order for the Markdown Easy filter to work properly.');
  }

}
