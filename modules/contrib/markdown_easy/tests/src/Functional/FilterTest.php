<?php

declare(strict_types=1);

namespace Drupal\Tests\markdown_easy\Functional;

use Drupal\Core\Session\AccountInterface;
use Drupal\filter\Entity\FilterFormat;
use Drupal\Tests\BrowserTestBase;

/**
 * Basic filter tests.
 *
 * @group markdown_easy
 */
final class FilterTest extends BrowserTestBase {

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

    $test_standard_markdown_format = FilterFormat::create([
      'format' => 'test_standard_markdown',
      'name' => 'Test Standard Markdown format',
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
    $test_standard_markdown_format->save();

    $this->drupalCreateNode([
      'title' => $this->randomString(),
      'id' => 1,
      'type' => 'article',
      'body' => [
        'value' => '**This should be strong.** _This is emphasized._ ~This is struck.~',
        'format' => 'test_standard_markdown',
      ],
    ])->save();

    $test_github_markdown_format = FilterFormat::create([
      'format' => 'test_github_markdown',
      'name' => 'Test GitHub Markdown format',
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
            'flavor' => 'github',
          ],
        ],
      ],
    ]);
    $test_github_markdown_format->save();

    $this->drupalCreateNode([
      'title' => $this->randomString(),
      'id' => 2,
      'type' => 'article',
      'body' => [
        'value' => '**This should be strong.** _This is emphasized._ ~This is struck.~',
        'format' => 'test_github_markdown',
      ],
    ])->save();
  }

  /**
   * Test the most basic functionality.
   *
   * @test
   */
  public function testBoldEmphasized(): void {
    $session = $this->assertSession();
    $this->drupalGet('/node/1');
    $session->statusCodeEquals(200);
    $session->elementExists('css', 'strong:contains("This should be strong.")');
    $session->elementExists('css', 'em:contains("This is emphasized.")');
    $session->elementNotExists('css', 'del:contains("This is struck.")');

    $this->drupalGet('/node/2');
    $session->statusCodeEquals(200);
    $session->elementExists('css', 'strong:contains("This should be strong.")');
    $session->elementExists('css', 'em:contains("This is emphasized.")');
    $session->elementExists('css', 'del:contains("This is struck.")');
  }

}
