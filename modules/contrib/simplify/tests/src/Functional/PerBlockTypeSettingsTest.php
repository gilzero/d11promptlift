<?php

namespace Drupal\Tests\simplify\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\block_content\Entity\BlockContentType;

/**
 * Test simplify per block-type settings.
 *
 * @group Simplify
 *
 * @ingroup simplify
 */
class PerBlockTypeSettingsTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['block_content', 'editor', 'simplify'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Simplify per block-type settings test.',
      'description' => 'Test the Simplify per block-type settings.',
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

    // Create a block type.
    $this->createBlockContentType('testing_type', TRUE);
  }

  /**
   * Perform full "per block-type" simplify scenario testing.
   */
  public function testSettingSaving() {

    /* -------------------------------------------------------.
     * 0/ Check that everything is here in the block type.
     */
    $this->drupalGet('block/add');

    $this->assertSession()->responseContains('About text formats');
    $this->assertSession()->responseContains('Revision information');

    /* -------------------------------------------------------.
     * 1/ Check if everything is there but unchecked.
     */

    // Globally activate some options.
    $this->drupalGet('admin/config/user-interface/simplify');
    $options = [
      'simplify_admin' => TRUE,
      'simplify_blocks_global[format]' => 'format',
    ];
    $this->submitForm($options, 'Save configuration');
    // Admin users setting.
    $this->assertSession()->checkboxChecked('edit-simplify-admin');

    /* -------------------------------------------------------.
     * 2/ Check the effect on block-type settings.
     */

    // Open admin UI.
    $this->drupalGet('admin/structure/block-content/manage/testing_type');

    // Blocks.
    $this->assertSession()->checkboxChecked('edit-simplify-blocks-format');
    $this->assertSession()->checkboxNotChecked('edit-simplify-blocks-revision-information');

    /* -------------------------------------------------------.
     * 2-bis/ Check if everything is properly disabled if needed.
     */

    // Block.
    $text_format = $this->xpath('//input[@name="simplify_blocks[format]" and @disabled="disabled"]');
    $this->assertTrue(count($text_format) === 1, 'Block text format option is disabled.');

    $revision_option = $this->xpath('//input[@name="simplify_block[revision-information]" and @disabled="disabled"]');
    $this->assertTrue(count($revision_option) === 0, 'Block revision information option is not disabled.');

    /* -------------------------------------------------------.
     * 3/ Save some "per block-type" options.
     */

    // Nodes.
    $options = [
      'simplify_blocks[revision_information]' => 'format',
    ];
    $this->submitForm($options, 'Save');

    /* -------------------------------------------------------.
     * 3-bis/ Check if options are saved.
     */
    $this->drupalGet('/admin/structure/block-content/manage/testing_type');
    $this->assertSession()->checkboxChecked('edit-simplify-blocks-revision-information');

    /* -------------------------------------------------------.
     * 4/ Check The effect of all this on node form.
     */
    $this->drupalGet('block/add/testing_type');

    $this->assertSession()->elementContains('css', '.js-filter-wrapper.hidden', 'About text formats');
    $this->assertSession()->responseNotContains('Revision information');
  }

  /**
   * Creates a custom block type (bundle).
   *
   * @param string $label
   *   The block type label.
   * @param bool $create_body
   *   Whether or not to create the body field.
   *
   * @return \Drupal\block_content\Entity\BlockContentType
   *   Created custom block type.
   */
  protected function createBlockContentType($label, $create_body = FALSE) {
    $bundle = BlockContentType::create([
      'id' => $label,
      'label' => $label,
      'revision' => TRUE,
    ]);
    $bundle->save();
    if ($create_body) {
      block_content_add_body_field($bundle->id());
    }
    return $bundle;
  }

}
