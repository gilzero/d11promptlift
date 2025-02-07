<?php

namespace Drupal\Tests\pwa_service_worker\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the pwa service worker functionalities.
 *
 * @group pwa_service_worker
 */
class PwaServiceWorkerTest extends WebDriverTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'test_page_test',
    'pwa',
    'pwa_service_worker',
  ];

  /**
   * A user with admin permissions.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $adminUser;

  /**
   * A user with authenticated permissions.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->config('system.site')->set('page.front', '/test-page')->save();

    $this->user = $this->drupalCreateUser([]);
    $this->adminUser = $this->drupalCreateUser([]);
    $this->adminUser->addRole($this->createAdminRole('admin', 'admin'));
    $this->adminUser->save();
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Test to see if the service worker successfully registers.
   */
  public function testServiceWorkerRegistered() {
    $this->assertSame(1, 1);
    // @todo Find a way how to test, that the service worker is registered.
    // navigator.serviceWorker.ready is async, so it will only return a Promise
    // and we can not wait for that promise outside of an async function.
    // The log seems to be unaccessible through a driver method.
    // And writing a custom script to capture the console.log content in a js
    // variable won't work either, the script will fire after the console.log
    // already applies.
  }

}
