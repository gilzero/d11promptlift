<?php

namespace Drupal\Tests\simplify\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test Simplify module global settings.
 *
 * @group Simplify
 *
 * @ingroup simplify
 */
class GlobalSettingsTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['simplify'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Simplify global settings test.',
      'description' => 'Test the Simplify module global settings page.',
      'group' => 'Simplify',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $admin_user = $this->drupalCreateUser(['administer simplify']);
    $this->drupalLogin($admin_user);
  }

  /**
   * Check that Simplify module global configuration files saves settings.
   */
  public function testSettingSaving() {

    // Open admin UI.
    $this->drupalGet('/admin/config/user-interface/simplify');

    /* -------------------------------------------------------.
     * 1/ Check only basic options are there but unchecked.
     */

    // Admin user option.
    $this->assertSession()->fieldExists('edit-simplify-admin');
    $this->assertSession()->checkboxNotChecked('edit-simplify-admin');
    // Node globals.
    $this->assertSession()->responseNotContains('Nodes');
    $this->assertSession()->fieldNotExists('edit-simplify-nodes-global-author');
    // User globals.
    $this->assertSession()->responseContains('Users');
    $this->assertSession()->checkboxNotChecked('edit-simplify-users-global-format');
    // Taxonomy is not here.
    $this->assertSession()->responseNotContains('Taxonomy');
    $this->assertSession()->fieldNotExists('edit-simplify-taxonomy-global-format');
    // Blocks is not here.
    $this->assertSession()->responseNotContains('Blocks');
    $this->assertSession()->fieldNotExists('edit-simplify-blocks-global-format');

    /* -------------------------------------------------------.
     * 2/ Check optional options are added if modules becomes available.
     */

    $this->container->get('module_installer')->install([
      'node',
      'taxonomy',
      'block',
      'comment',
      'menu_ui',
      'path',
    ], TRUE);
    drupal_flush_all_caches();
    $this->drupalGet('/admin/config/user-interface/simplify');
    // Node globals.
    $this->assertSession()->responseContains('Nodes');
    $this->assertSession()->checkboxNotChecked('edit-simplify-nodes-global-author');
    $this->assertSession()->checkboxNotChecked('edit-simplify-nodes-global-format');
    $this->assertSession()->checkboxNotChecked('edit-simplify-nodes-global-options');
    $this->assertSession()->checkboxNotChecked('edit-simplify-nodes-global-revision-information');
    // User globals.
    $this->assertSession()->responseContains('Users');
    $this->assertSession()->checkboxNotChecked('edit-simplify-users-global-format');
    $this->assertSession()->checkboxNotChecked('edit-simplify-users-global-status');
    // Taxonomy is not here.
    $this->assertSession()->responseContains('Taxonomy');
    $this->assertSession()->checkboxNotChecked('edit-simplify-taxonomies-global-format');
    $this->assertSession()->checkboxNotChecked('edit-simplify-taxonomies-global-relations');
    $this->assertSession()->checkboxNotChecked('edit-simplify-taxonomies-global-path');
    // Blocks is not here.
    $this->assertSession()->responseContains('Block');
    $this->assertSession()->checkboxNotChecked('edit-simplify-blocks-global-format');

    /*  -------------------------------------------------------.
     * 3/ Check and validate some options.
     */

    $options = [
      'simplify_admin' => TRUE,
      'simplify_nodes_global[author]' => 'author',
      'simplify_nodes_global[comment]' => 'comment',
      'simplify_nodes_global[options]' => 'options',
      'simplify_taxonomies_global[format]' => 'format',
      'simplify_taxonomies_global[path]' => 'path',
    ];
    $this->submitForm($options, 'Save configuration');
    // User1.
    $this->assertSession()->checkboxChecked('edit-simplify-admin');
    // Nodes.
    $this->assertSession()->checkboxChecked('edit-simplify-nodes-global-author');
    $this->assertSession()->checkboxNotChecked('edit-simplify-nodes-global-format');
    $this->assertSession()->checkboxChecked('edit-simplify-nodes-global-options');
    $this->assertSession()->checkboxNotChecked('edit-simplify-nodes-global-revision-information');
    $this->assertSession()->checkboxChecked('edit-simplify-nodes-global-comment');
    $this->assertSession()->checkboxNotChecked('edit-simplify-nodes-global-menu');
    $this->assertSession()->checkboxNotChecked('edit-simplify-nodes-global-path');
    // Vocabularies.
    $this->assertSession()->checkboxChecked('edit-simplify-taxonomies-global-format');
    $this->assertSession()->checkboxNotChecked('edit-simplify-taxonomies-global-relations');
    $this->assertSession()->checkboxChecked('edit-simplify-taxonomies-global-path');
  }

}
