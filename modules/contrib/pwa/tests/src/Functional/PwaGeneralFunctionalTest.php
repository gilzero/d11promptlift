<?php

namespace Drupal\Tests\pwa\Functional;

use Drupal\Component\Serialization\Json;

/**
 * This class provides methods specifically for testing something.
 *
 * @group pwa
 */
class PwaGeneralFunctionalTest extends PwaFunctionalTestBase {

  /**
   * Tests if installing the module, won't break the site.
   */
  public function testInstallation() {
    $session = $this->assertSession();
    $this->drupalGet('<front>');
    // Ensure the status code is success:
    $session->statusCodeEquals(200);
    // Ensure the correct test page is loaded as front page:
    $session->pageTextContains('Test page text.');
  }

  /**
   * Tests if uninstalling the module, won't break the site.
   */
  public function testUninstallation() {
    // Go to uninstallation page an uninstall pwa:
    $session = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->drupalGet('/admin/modules/uninstall');
    $session->statusCodeEquals(200);
    $page->checkField('edit-uninstall-pwa');
    $page->pressButton('edit-submit');
    $session->statusCodeEquals(200);
    // Confirm uninstall:
    $page->pressButton('edit-submit');
    $session->statusCodeEquals(200);
    $session->pageTextContains('The selected modules have been uninstalled.');
    // Retest the frontpage:
    $this->drupalGet('<front>');
    // Ensure the status code is success:
    $session->statusCodeEquals(200);
    // Ensure the correct test page is loaded as front page:
    $session->pageTextContains('Test page text.');
  }

  /**
   * Tests, if the settings can only be accessed with the correct permissions.
   */
  public function testSettingsPermission() {
    $session = $this->assertSession();
    // Go to the manifest settings page and see if we have access as admin:
    $this->drupalGet('/admin/config/services/pwa/manifest');
    $session->statusCodeEquals('200');
    $session->pageTextContains('Manifest configuration');
    // Check that anonymous user can not access the config page:
    $this->drupalLogout();
    $this->drupalGet('/admin/config/services/pwa/manifest');
    $session->statusCodeEquals('403');
    $session->pageTextNotContains('Manifest configuration');
    // Check that a user with the correct permission has access:
    $this->drupalLogin($this->user);
    $this->drupalGet('/admin/config/services/pwa/manifest');
    $session->statusCodeEquals('200');
    $session->pageTextContains('Manifest configuration');
  }

  /**
   * Test to see if the manifest file is attached to the main page.
   */
  public function testManifestAvailable() {
    // Check if the manifest.json is linked as an admin user:
    $this->drupalGet('<front>');
    $this->assertSession()->elementExists('css', 'link[rel="manifest"]');
    $this->assertSession()->elementAttributeContains('css', 'link[rel="manifest"]', 'href', '/manifest.json');
    $this->drupalLogout();
    // Check if the manifest.json is NOT linked as an anonymous user, (as they
    // don't have the 'access pwa' permission):
    $this->drupalGet('<front>');
    $this->assertSession()->elementNotExists('css', 'link[rel="manifest"]');
    // Login as a normal authenticated user with the 'access pwa' permission
    // and see if they have the link attached:
    $this->drupalLogin($this->user);
    $this->drupalGet('<front>');
    $this->assertSession()->elementExists('css', 'link[rel="manifest"]');
    $this->assertSession()->elementAttributeContains('css', 'link[rel="manifest"]', 'href', '/manifest.json');
  }

  /**
   * Test caching of manifest.json.
   */
  public function testManifestJsonCache() {
    $this->drupalGet('/manifest.json');
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    // Check if the manifest.json is cacheable:
    $session->responseHeaderContains('X-Drupal-Cache-Tags', 'manifestjson');
  }

  /**
   * Tests the default manifest settings.
   */
  public function testManifestDefaultSettings() {
    $config = $this->config('pwa.config');
    $response = $this->drupalGet('/manifest.json');
    $data = JSON::decode($response);
    $session = $this->assertSession();
    $session->statusCodeEquals(200);

    // Check if the default setting are used:
    $this->assertSame($config->get('name'), $data['name']);
    $this->assertSame($config->get('short_name'), $data['short_name']);
    $this->assertSame($config->get('start_url'), $data['start_url']);
    $this->assertSame($config->get('scope'), $data['scope']);
    $this->assertSame($config->get('theme_color'), $data['theme_color']);
    $this->assertSame($config->get('background_color'), $data['background_color']);
    $this->assertSame($config->get('display'), $data['display']);
    $this->assertSame($config->get('orientation'), $data['orientation']);
  }

  /**
   * Tests setting the manifest json config via code.
   */
  public function testManifestJsonSettings() {
    $config = $this->config('pwa.config');
    $session = $this->assertSession();
    $session->statusCodeEquals(200);

    // Change the manifest config, and see if it takes effect in the
    // manifest.json:
    $config
      ->set('name', 'myLongSiteName')
      ->set('short_name', 'myShortName')
      ->set('start_url', '/app')
      ->set('scope', '/scope')
      ->set('theme_color', '#e3a660')
      ->set('background_color', '#52b6e7')
      ->set('display', 'browser')
      ->set('description', 'My wonderful app')
      ->set('orientation', 'landscape')
      ->set('lang', 'EN-gb')
      ->set('dir', 'ltr')
      ->save();
    \Drupal::service('cache_tags.invalidator')->invalidateTags(['manifestjson']);

    $response = $this->drupalGet('/manifest.json');
    $data = JSON::decode($response);
    // Check if the settings applied:
    $this->assertSame('myLongSiteName', $data['name']);
    $this->assertSame('myShortName', $data['short_name']);
    $this->assertSame('/app', $data['start_url']);
    $this->assertSame('/scope', $data['scope']);
    $this->assertSame('#e3a660', $data['theme_color']);
    $this->assertSame('#52b6e7', $data['background_color']);
    $this->assertSame('browser', $data['display']);
    $this->assertSame('landscape', $data['orientation']);
    $this->assertSame('EN-gb', $data['lang']);
    $this->assertSame('ltr', $data['dir']);
  }

  /**
   * Tests if the manifest settings through ui will actually apply.
   */
  public function testManifestJsonSettingsPage() {
    $this->drupalGet('/admin/config/services/pwa/manifest');
    $page = $this->getSession()->getPage();
    $session = $this->assertSession();
    $page->fillField('edit-name', 'myLongSiteName');
    $page->fillField('edit-short-name', 'myShortName');
    $page->fillField('edit-description', 'My wonderful app');
    $page->fillField('edit-start-url', '/app');
    $page->fillField('edit-scope', '/scope');
    $page->fillField('edit-lang', 'EN-gb');
    $page->fillField('edit-theme-color', '#e3a660');
    $page->fillField('edit-background-color', '#52b6e7');
    $page->fillField('edit-display', 'browser');
    $page->selectFieldOption('edit-orientation-natural', 'natural');
    $page->selectFieldOption('edit-dir-ltr', 'ltr');
    $page->pressButton('edit-submit');
    $session->pageTextContains('The configuration options have been saved.');
    $session->statusCodeEquals(200);

    // Check the manifest.json if all settings applied correctly:
    $response = $this->drupalGet('/manifest.json');
    $data = JSON::decode($response);
    $session->statusCodeEquals(200);

    $this->assertSame('myLongSiteName', $data['name']);
    $this->assertSame('myShortName', $data['short_name']);
    $this->assertSame('/app', $data['start_url']);
    $this->assertSame('/scope', $data['scope']);
    $this->assertSame('EN-gb', $data['lang']);
    $this->assertSame('ltr', $data['dir']);
    $this->assertSame('#e3a660', $data['theme_color']);
    $this->assertSame('#52b6e7', $data['background_color']);
    $this->assertSame('browser', $data['display']);
    $this->assertSame('My wonderful app', $data['description']);
    $this->assertSame('natural', $data['orientation']);
  }

  /**
   * Tests the behavior of the 'listed_only' manifest path mode.
   */
  public function testManifestAddedOnSpecificPagesPathModeListedOnly() {
    $session = $this->assertSession();
    $this->config('pwa.config')
      ->set('manifest_path_mode', 'listed_only')
      ->set('manifest_paths', '')
      ->save();
    \Drupal::cache('render')->invalidateAll();
    \Drupal::cache('dynamic_page_cache')->invalidateAll();
    // Leaving the "manifest_paths" empty should add the manifest.json on
    // every page:
    $this->drupalGet('/admin');
    $session->statusCodeEquals(200);
    $session->elementExists('css', 'head > link[rel="manifest"]');
    $this->drupalGet('<front>');
    $session->statusCodeEquals(200);
    $session->elementExists('css', 'head > link[rel="manifest"]');
    $this->drupalGet('/admin/config/system');
    $session->statusCodeEquals(200);
    $session->elementExists('css', 'head > link[rel="manifest"]');

    // Set a specific path to apply the manifest.json to:
    $this->config('pwa.config')
      ->set('manifest_paths', '/admin')
      ->save();
    \Drupal::cache('render')->invalidateAll();
    \Drupal::cache('dynamic_page_cache')->invalidateAll();

    // Check if the manifest is added to desired page:
    $this->drupalGet('/admin');
    $session->statusCodeEquals(200);
    $session->elementExists('css', 'head > link[rel="manifest"]');
    // Any other page should be excluded:
    $this->drupalGet('<front>');
    $session->statusCodeEquals(200);
    $session->elementNotExists('css', 'head > link[rel="manifest"]');
    // Even the sub pages of "/admin":
    $this->drupalGet('/admin/config/system');
    $session->statusCodeEquals(200);
    $session->elementNotExists('css', 'head > link[rel="manifest"]');

    // If we use wildcard modifiers, also the sub pages should have a
    // manifest.json:
    $this->config('pwa.config')
      ->set('manifest_paths', "/admin\n/admin/*")
      ->save();
    \Drupal::cache('render')->invalidateAll();
    \Drupal::cache('dynamic_page_cache')->invalidateAll();
    // Check if the manifest is added to desired page:
    $this->drupalGet('/admin');
    $session->statusCodeEquals(200);
    $session->elementExists('css', 'head > link[rel="manifest"]');
    // Sub pages should also have a manifest.json now:
    $this->drupalGet('/admin/config/system');
    $session->statusCodeEquals(200);
    $session->elementExists('css', 'head > link[rel="manifest"]');
    // Any other page should be excluded:
    $this->drupalGet('<front>');
    $session->statusCodeEquals(200);
    $session->elementNotExists('css', 'head > link[rel="manifest"]');
  }

  /**
   * Tests the behavior of the 'all_except_listed' manifest path mode.
   */
  public function testManifestAddedOnSpecificPagesPathModeAllExceptListed() {
    $session = $this->assertSession();
    $this->config('pwa.config')
      ->set('manifest_path_mode', 'all_except_listed')
      ->set('manifest_paths', '')
      ->save();
    \Drupal::cache('render')->invalidateAll();
    \Drupal::cache('dynamic_page_cache')->invalidateAll();
    // Leaving the "manifest_paths" empty should add the manifest.json on
    // every page:
    $this->drupalGet('/admin');
    $session->statusCodeEquals(200);
    $session->elementExists('css', 'head > link[rel="manifest"]');
    $this->drupalGet('<front>');
    $session->statusCodeEquals(200);
    $session->elementExists('css', 'head > link[rel="manifest"]');
    $this->drupalGet('/admin/config/system');
    $session->statusCodeEquals(200);
    $session->elementExists('css', 'head > link[rel="manifest"]');

    // Set a specific path to avoid applying the manifest.json to:
    $this->config('pwa.config')
      ->set('manifest_paths', '/admin')
      ->save();
    \Drupal::cache('render')->invalidateAll();
    \Drupal::cache('dynamic_page_cache')->invalidateAll();

    // Check if the manifest is not added to the specified page:
    $this->drupalGet('/admin');
    $session->statusCodeEquals(200);
    $session->elementNotExists('css', 'head > link[rel="manifest"]');
    // Any other page should have the manifest.json applied:
    $this->drupalGet('<front>');
    $session->statusCodeEquals(200);
    $session->elementExists('css', 'head > link[rel="manifest"]');
    // Even the sub pages of the excluded path:
    $this->drupalGet('/admin/config/system');
    $session->statusCodeEquals(200);
    $session->elementExists('css', 'head > link[rel="manifest"]');

    // If we use wildcard modifiers, also the sub pages should not have a
    // manifest.json:
    $this->config('pwa.config')
      ->set('manifest_paths', "/admin\n/admin/*")
      ->save();
    \Drupal::cache('render')->invalidateAll();
    \Drupal::cache('dynamic_page_cache')->invalidateAll();
    // Check if the manifest is not added to the specified page:
    $this->drupalGet('/admin');
    $session->statusCodeEquals(200);
    $session->elementNotExists('css', 'head > link[rel="manifest"]');
    // Nor any sub pages:
    $this->drupalGet('/admin/config/system');
    $session->statusCodeEquals(200);
    $session->elementNotExists('css', 'head > link[rel="manifest"]');
    // Any other page should have the manifest:
    $this->drupalGet('<front>');
    $session->statusCodeEquals(200);
    $session->elementExists('css', 'head > link[rel="manifest"]');
  }

  // @todo Add test for the cross origin setting here.
}
