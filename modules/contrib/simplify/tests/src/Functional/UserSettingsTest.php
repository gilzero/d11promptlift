<?php

namespace Drupal\Tests\simplify\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test simplify user settings.
 *
 * @group Simplify
 *
 * @ingroup simplify
 */
class UserSettingsTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['contact', 'user', 'simplify'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Simplify user settings test.',
      'description' => 'Test the Simplify module user settings.',
      'group' => 'Simplify',
    ];
  }

  /**
   * Perform full user simplify scenario testing.
   */
  public function testSettingSaving() {

    // Create an admin user.
    $admin_user = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($admin_user);

    $user_edit_page = '/user/' . $admin_user->id() . '/edit';

    /* -------------------------------------------------------.
     * 0/ Check that everything is here in the user edit page.
     */
    // A- On user edit page.
    $this->drupalGet($user_edit_page);
    $this->assertSession()->responseContains('Status');
    $this->assertSession()->responseContains('Contact settings');
    $this->assertSession()->responseContains('Locale settings');
    // B- On user register page.
    $this->drupalLogout();
    $this->drupalGet('/user/register');
    $this->assertSession()->responseContains('Contact settings');

    /* -------------------------------------------------------.
     * 1/ Check if everything is there but unchecked.
     */
    $this->drupalLogin($admin_user);
    // Globally activate some options.
    $this->drupalGet('admin/config/user-interface/simplify');
    $options = [
      'simplify_admin' => TRUE,
      'simplify_users_global[status]' => 'status',
      'simplify_users_global[timezone]' => 'timezone',
      'simplify_users_global[contact]' => 'contact',
    ];
    $this->submitForm($options, 'Save configuration');
    // Admin users setting.
    $this->assertSession()->checkboxChecked('edit-simplify-admin');

    /* -------------------------------------------------------.
     * 2/ Check the effect on user settings.
     */

    // @todo Remove this when hook_form_user_register_alter() is taken in
    // in consideration in testing profile with no cache refresh.
    drupal_flush_all_caches();

    // A- On user edit page.
    $this->drupalGet($user_edit_page);
    // $this->assertSession()->responseContains('Contact settings');
    $this->assertSession()->elementContains('css', '#edit-contact.visually-hidden', 'Contact settings');
    $this->assertSession()->elementContains('css', '#edit-timezone.visually-hidden', 'Locale settings');
    // B- On user register page.
    $this->drupalLogout();
    $this->drupalGet('/user/register');
    $this->assertSession()->elementContains('css', '#edit-contact.visually-hidden', 'Contact settings');
    $this->assertSession()->responseNotContains('Locale settings');
  }

}
