<?php

declare(strict_types=1);

namespace Drupal\Tests\markdown_easy\Functional;

use Drupal\Core\Session\AccountInterface;
use Drupal\filter\Entity\FilterFormat;
use Drupal\Tests\BrowserTestBase;

/**
 * Filter warning tests.
 *
 * @group markdown_easy
 */
final class FilterWarningsTest extends BrowserTestBase {

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
   * A user with admin permissions.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected AccountInterface $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->createContentType(['type' => 'article', 'name' => 'Article']);

    $this->adminUser = $this->drupalCreateUser();
    $this->adminUser->addRole($this->createAdminRole('admin', 'admin'));
    $this->adminUser->save();
    $this->drupalLogin($this->adminUser);

    $test_securely_configured_format = FilterFormat::create([
      'format' => 'securely_configured_format',
      'name' => 'Secure Markdown format',
      'weight' => 1,
      'filters' => [
        'filter_html' => [
          'status' => TRUE,
          'weight' => 2,
          'settings' => [
            'allowed_html' => "<strong> <em> <del>",
          ],
        ],
        'filter_autop' => [
          'status' => TRUE,
          'weight' => 1,
        ],
        'markdown_easy' => [
          'status' => TRUE,
          'weight' => 0,
          'settings' => [
            'flavor' => 'standard',
          ],
        ],
      ],
    ]);
    $test_securely_configured_format->save();

    $this->drupalCreateNode([
      'title' => $this->randomString(),
      'id' => 1,
      'type' => 'article',
      'body' => [
        'value' => '**This should be strong.** _This is emphasized._ ~This is struck.~',
        'format' => 'securely_configured_format',
      ],
    ])->save();

    $test_not_securely_configured_format_1 = FilterFormat::create([
      'format' => 'not_securely_configured_format_1',
      'name' => 'Not secure Markdown format',
      'weight' => 1,
      'filters' => [
        'filter_html' => [
          'status' => TRUE,
          'weight' => -1,
          'settings' => [
            'allowed_html' => "<strong> <em> <del>",
          ],
        ],
        'filter_autop' => [
          'status' => TRUE,
          'weight' => 1,
        ],
        'markdown_easy' => [
          'status' => TRUE,
          'weight' => 2,
          'settings' => [
            'flavor' => 'github',
          ],
        ],
      ],
    ]);
    $test_not_securely_configured_format_1->save();

    $this->drupalCreateNode([
      'title' => $this->randomString(),
      'id' => 2,
      'type' => 'article',
      'body' => [
        'value' => '**This should be strong.** _This is emphasized._ ~This is struck.~',
        'format' => 'not_securely_configured_format_1',
      ],
    ])->save();
  }

  /**
   * Test that the Status Report shows the Markdown Easy warning.
   *
   * @test
   */
  public function testStatusReport(): void {
    $session = $this->assertSession();
    $this->drupalGet('/admin/reports/status');
    $session->statusCodeEquals(200);
    $session->elementExists('css', '.system-status-report__entry__value .description:contains("The ""Limit allowed HTML tags and correct faulty HTML"" filter is strongly recommended and should be configured to run after the Markdown Easy filter.")');
    $session->elementExists('css', '.system-status-report__entry__value .description:contains("The ""Convert line breaks into HTML"" filter is recommended and should be configured to run after the Markdown Easy filter.")');
  }

}
