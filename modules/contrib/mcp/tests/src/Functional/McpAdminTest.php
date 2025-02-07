<?php

namespace Drupal\Tests\mcp\Functional;

use Drupal\user\UserInterface;
use Drupal\Tests\BrowserTestBase;

/**
 * Test Admin interface for the MCP module.
 *
 * @group mcp
 */
class McpAdminTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['mcp', 'mcp_content'];

  /**
   * A user with the 'administer site configuration' permission.
   *
   * @var \Drupal\user\UserInterface
   */
  protected UserInterface $user;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->user = $this->drupalCreateUser(
      ['administer site configuration', 'access content']
    );
  }

  /**
   * Tests the MCP configuration page.
   */
  public function testMcpConfigPageExists() {
    $this->drupalLogin($this->user);

    // Go to the MCP configuration page.
    $this->drupalGet('/admin/config/mcp');
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Tests the MCP configuration form.
   */
  public function testConfigFormSse() {
    $this->drupalLogin($this->user);

    // Go to the MCP configuration page.
    $this->drupalGet('/admin/config/mcp');
    $this->assertSession()->statusCodeEquals(200);

    // The form 'enable_sse' should be a checkbox with a default value of TRUE.
    $this->assertSession()->checkboxChecked('enable_sse');

    // Unchecked the checkbox and submit the form.
    $this->submitForm(
      [
        'enable_sse' => FALSE,
      ],
      'Save configuration'
    );

    // Check if configuration is saved.
    $this->drupalGet('/admin/config/mcp');
    $this->assertSession()->checkboxNotChecked('enable_sse');
  }

  /**
   * Tests the MCP configuration form.
   */
  public function testConfigFormPluginConfig() {
    $this->drupalLogin($this->user);

    // Go to the MCP configuration page.
    $this->drupalGet('/admin/config/mcp');
    $this->assertSession()->statusCodeEquals(200);

    // The form should have a container for plugin settings.
    $this->assertSession()->elementExists('css', '#edit-plugins-wrapper');

    // The form should display vertical tabs, with a default tab labeled
    // "General MCP". This tab should contain a subform that includes a checkbox
    // set to TRUE by default.
    $this->assertSession()->elementExists(
      'css', '#edit-plugins-wrapper #edit-plugins-wrapper-general'
    );
    $this->assertSession()->checkboxChecked(
      'plugins_wrapper[general][enabled]'
    );

    // Now if enable node module, new plugin tab should be visible with a form
    // With a checkbox set to TRUE by default and Checkboxes for each node type
    // with a default value of TRUE.
    $this->container->get('module_installer')->install(['node']);

    // Create one page content  type.
    $this->drupalCreateContentType(['type' => 'page']);
    $this->drupalCreateContentType(['type' => 'article']);

    $this->drupalGet('/admin/config/mcp');
    $this->assertSession()->elementExists(
      'css', '#edit-plugins-wrapper #edit-plugins-wrapper-content'
    );
    $this->assertSession()->checkboxChecked(
      'plugins_wrapper[content][enabled]'
    );
    $this->assertSession()->checkboxChecked(
      'plugins_wrapper[content][config][content_types][article]'
    );
    $this->assertSession()->checkboxChecked(
      'plugins_wrapper[content][config][content_types][page]'
    );
  }

}
