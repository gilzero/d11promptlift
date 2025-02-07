<?php

namespace Drupal\Tests\simplify\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\comment\Tests\CommentTestTrait;

/**
 * Test simplify per comment-type settings.
 *
 * @group Simplify
 *
 * @ingroup simplify
 */
class PerCommentTypeSettingsTest extends BrowserTestBase {
  use CommentTestTrait;

  /**
   * An administrative user with permission to configure comment settings.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['node', 'comment', 'field_ui', 'simplify'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Simplify per comment-type settings test.',
      'description' => 'Test the Simplify per comment-type settings.',
      'group' => 'Simplify',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create two test users.
    $this->adminUser = $this->drupalCreateUser([
      'administer content types',
      'administer comments',
      'administer comment types',
      'administer comment fields',
      'administer comment display',
      'administer simplify',
      'skip comment approval',
      'post comments',
      'access comments',
      'access content',
    ]);
    $this->drupalLogin($this->adminUser);

    // Create a content type.
    $this->drupalCreateContentType(['type' => 'content_type', 'name' => 'Testing content type']);

    // Create comment field on $content_type bundle.
    $this->addDefaultCommentField('node', 'content_type');
  }

  /**
   * Check that Simplify module global configuration files saves settings.
   */
  public function testSettingSaving() {

    /* -------------------------------------------------------.
     * 0/ Check the comment form by default.
     */

    // Create a test node authored by the user.
    $node = $this->drupalCreateNode([
      'type' => 'content_type',
      'promote' => 1,
      'uid' => $this->adminUser->id(),
    ]);

    // Check if options are there.
    $this->drupalGet("/node/" . $node->id());
    $this->assertSession()->responseContains('About text formats');

    /* -------------------------------------------------------.
     * 1/ Activate some global options and check "per comment-type" accordingly.
     */

    // Globally activate some options.
    $this->drupalGet('/admin/config/user-interface/simplify');
    $options = [
      'simplify_admin' => TRUE,
      'simplify_comments_global[format]' => 'format',
    ];
    $this->submitForm($options, 'Save configuration');

    // Open admin UI.
    $this->drupalGet('/admin/structure/comment/manage/comment');

    // Check if global options are forwarded.
    $this->assertSession()->checkboxChecked('edit-simplify-comments-format');

    // Check if everything is properly disabled if needed.
    $text_format = $this->xpath('//input[@name="simplify_comments[format]" and @disabled="disabled"]');
    $this->assertTrue(count($text_format) === 1, 'Comment text format option is disabled.');

    /* -------------------------------------------------------.
     * 2/ Remove global options.
     */

    $this->drupalGet('/admin/config/user-interface/simplify');
    $options = [
      'simplify_admin' => TRUE,
      'simplify_comments_global[format]' => FALSE,
    ];
    $this->submitForm($options, 'Save configuration');

    // Open admin UI.
    $this->drupalGet('/admin/structure/comment/manage/comment');

    // Check if global options are forwarded.
    $this->assertSession()->checkboxNotChecked('edit-simplify-comments-format');

    /* -------------------------------------------------------.
     * 3/ Save some custom options.
     */

    // Nodes.
    $options = [
      'simplify_comments[format]' => 'format',
    ];
    $this->submitForm($options, 'Save');

    /* -------------------------------------------------------.
     * 4/ Check if options are saved.
     */
    $this->drupalGet('/admin/structure/comment/manage/comment');
    $this->assertSession()->checkboxChecked('edit-simplify-comments-format');

    /*
     * 5/ Check if comment form is now simplified.
     */
    $this->drupalGet("/node/" . $node->id());
    $this->assertSession()->elementContains('css', '.js-filter-wrapper.hidden', 'About text formats');
  }

}
