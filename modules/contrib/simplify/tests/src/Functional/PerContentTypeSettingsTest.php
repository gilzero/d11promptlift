<?php

namespace Drupal\Tests\simplify\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test simplify per content-type settings.
 *
 * @group Simplify
 *
 * @ingroup simplify
 */
class PerContentTypeSettingsTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'path',
    'menu_ui',
    'comment',
    'node',
    'user',
    'simplify',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Simplify per content-type settings test.',
      'description' => 'Test the Simplify per content-type settings.',
      'group' => 'Simplify',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create an admin user.
    $admin_user = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($admin_user);

    // Create a content type.
    $this->drupalCreateContentType(['type' => 'testing_type', 'name' => 'Testing type']);

    // Create another content type.
    $this->drupalCreateContentType(['type' => 'another_type', 'name' => 'Another type']);
  }

  /**
   * Perform full "per content-type" simplify scenario testing.
   */
  public function testSettingSaving() {

    /* -------------------------------------------------------.
     * 0/ Check that everything is here in the content type.
     */
    $this->drupalGet('node/add/testing_type');

    $this->assertSession()->responseContains('About text formats');
    $this->assertSession()->responseContains('Menu settings');
    $this->assertSession()->responseContains('Authoring information');
    $this->assertSession()->responseContains('Promotion options');

    /* -------------------------------------------------------.
     * 1/ Check if everything is there but unchecked.
     */

    // Globally activate some options.
    $this->drupalGet('admin/config/user-interface/simplify');
    $options = [
      'simplify_admin' => TRUE,
      'simplify_nodes_global[author]' => 'author',
      'simplify_nodes_global[comment]' => 'comment',
      'simplify_nodes_global[options]' => 'options',
    ];
    $this->submitForm($options, 'Save configuration');
    // Admin users setting.
    $this->assertSession()->checkboxChecked('edit-simplify-admin');

    /* -------------------------------------------------------.
     * 2/ Check the effect on content-type settings.
     */

    // Open admin UI.
    $this->drupalGet('/admin/structure/types/manage/testing_type');

    // Nodes.
    $this->assertSession()->checkboxChecked('edit-simplify-nodes-author');
    $this->assertSession()->checkboxNotChecked('edit-simplify-nodes-format');
    $this->assertSession()->checkboxChecked('edit-simplify-nodes-options');
    $this->assertSession()->checkboxNotChecked('edit-simplify-nodes-revision-information');
    $this->assertSession()->checkboxChecked('edit-simplify-nodes-comment');

    /* -------------------------------------------------------.
     * 2-bis/ Check if everything is properly disabled if needed.
     */

    // Nodes.
    $author_info = $this->xpath('//input[@name="simplify_nodes[author]" and @disabled="disabled"]');
    $this->assertTrue(count($author_info) === 1, 'Node authoring information option is disabled.');

    $text_format = $this->xpath('//input[@name="simplify_nodes[format]" and @disabled="disabled"]');
    $this->assertTrue(count($text_format) === 0, 'Node text format option is not disabled.');

    $publishing_option = $this->xpath('//input[@name="simplify_nodes[options]" and @disabled="disabled"]');
    $this->assertTrue(count($publishing_option) === 1, 'Node promoting options option is disabled.');

    $revision_option = $this->xpath('//input[@name="simplify_nodes[revision-information]" and @disabled="disabled"]');
    $this->assertTrue(count($revision_option) === 0, 'Node revision information option is not disabled.');

    $comment_option = $this->xpath('//input[@name="simplify_nodes[comment]" and @disabled="disabled"]');
    $this->assertTrue(count($comment_option) === 1, 'Node comment settings option is disabled.');

    /* -------------------------------------------------------.
     * 3/ Save some "per content-type" options.
     */

    // Nodes.
    $options = [
      'simplify_nodes[format]' => 'format',
    ];
    $this->submitForm($options, 'Save');

    /* -------------------------------------------------------.
     * 3-bis/ Check if options are saved.
     */
    $this->drupalGet('admin/structure/types/manage/testing_type');
    $this->assertSession()->checkboxChecked('edit-simplify-nodes-format');

    /* -------------------------------------------------------.
     * 4/ Check The effect of all this on node form.
     */
    $this->drupalGet('node/add/testing_type');

    $this->assertSession()->elementContains('css', '.js-filter-wrapper.hidden', 'About text formats');
    $this->assertSession()->responseContains('Menu settings');
    $this->assertSession()->elementContains('css', '.node-form-author.visually-hidden', 'Authoring information');
    $this->assertSession()->elementContains('css', '.node-form-options.visually-hidden', 'Promotion options');
  }

}
