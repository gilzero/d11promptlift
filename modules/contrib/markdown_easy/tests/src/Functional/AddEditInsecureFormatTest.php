<?php

declare(strict_types=1);

namespace Drupal\Tests\markdown_easy\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test warnings when adding/editing a text format with no validation.
 *
 * @group markdown_easy
 */
class AddEditInsecureFormatTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'markdown_easy_test',
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
   */
  public function testAddEditFormat(): void {
    // Start the browsing session.
    $session = $this->assertSession();

    // Navigate to the add text format page.
    $this->drupalGet('/admin/config/content/formats/add');
    $session->statusCodeEquals(200);

    // Populate the new text format form.
    $edit = [
      'name' => 'Markdown Easy format page test',
      'format' => 'markdown_easy_format_page_test',
      'filters[markdown_easy][status]' => 1,
      'filters[markdown_easy][settings][flavor]' => 'standard',
    ];
    $this->submitForm($edit, 'Save configuration');
    $session->statusCodeEquals(200);
    $session->pageTextContainsOnce('Added text format Markdown Easy format page test.');
    $session->pageTextContainsOnce('The text format Markdown Easy format page test is potentially configured insecurely. The "Limit allowed HTML tags and correct faulty HTML" filter is strongly recommended and should be configured to run after the Markdown Easy filter.');
    $session->pageTextContainsOnce('The text format Markdown Easy format page test is potentially configured incorrectly. The "Convert line breaks into HTML" filter is recommended and should be configured to run after the Markdown Easy filter.');

    // Navigate back to the edit text format page.
    $this->drupalGet('/admin/config/content/formats/manage/markdown_easy_format_page_test');
    $session->statusCodeEquals(200);
    $this->submitForm($edit, 'Save configuration');
    $session->statusCodeEquals(200);
    $session->pageTextContainsOnce('The text format Markdown Easy format page test has been updated.');
    $session->pageTextContainsOnce('The text format Markdown Easy format page test is potentially configured insecurely. The "Limit allowed HTML tags and correct faulty HTML" filter is strongly recommended and should be configured to run after the Markdown Easy filter.');
    $session->pageTextContainsOnce('The text format Markdown Easy format page test is potentially configured incorrectly. The "Convert line breaks into HTML" filter is recommended and should be configured to run after the Markdown Easy filter.');

    // Navigate back to the edit text format page.
    $this->drupalGet('/admin/config/content/formats/manage/markdown_easy_format_page_test');
    $session->statusCodeEquals(200);
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
    $session->pageTextContainsOnce('The text format Markdown Easy format page test has been updated.');
    $session->pageTextNotContains('The text format Markdown Easy format page test is potentially configured insecurely. The "Limit allowed HTML tags and correct faulty HTML" filter is strongly recommended and should be configured to run after the Markdown Easy filter.');
    $session->pageTextNotContains('The text format Markdown Easy format page test is potentially configured incorrectly. The "Convert line breaks into HTML" filter is recommended and should be configured to run after the Markdown Easy filter.');
  }

}
