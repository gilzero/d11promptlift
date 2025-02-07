<?php

declare(strict_types=1);

namespace Drupal\Tests\markdown_easy\Functional;

use Drupal\Core\Session\AccountInterface;
use Drupal\filter\Entity\FilterFormat;
use Drupal\Tests\BrowserTestBase;

/**
 * Test that the Markdown processor config is overridable.
 *
 * @group markdown_easy
 */
final class ConfigModifyHookTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'markdown_easy_hook_test',
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
        'value' => '**This should be strong.** <a href="javascript:alert(\'xss\')">)I am a bad link.</a> _This is emphasized._ <p>Did I sneak in?</p> ~This is struck.~',
        'format' => 'test_standard_markdown',
      ],
    ])->save();
  }

  /**
   * Test that the library config can be overridden.
   *
   * @test
   */
  public function testOverrideMarkdownLibraryConfig(): void {
    $session = $this->assertSession();
    $this->drupalGet('/node/1');
    $session->statusCodeEquals(200);
    $session->elementExists('css', 'strong:contains("This should be strong.")');
    $session->elementExists('css', 'em:contains("This is emphasized.")');
    $session->elementNotExists('css', 'del:contains("This is struck.")');
    $session->elementExists('css', 'a:contains("I am a bad link.")');
    $session->elementExists('css', 'p:contains("Did I sneak in?")');
  }

}
