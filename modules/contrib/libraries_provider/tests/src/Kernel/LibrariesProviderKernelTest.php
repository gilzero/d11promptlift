<?php

namespace Drupal\Tests\libraries_provider\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\libraries_provider\Autoservice\LibrariesProviderManager;
use Drupal\libraries_provider\Entity\Library;
use Drupal\Core\Asset\LibraryDiscoveryInterface;

/**
 * Kernel tests for Libraries Provider functions.
 *
 * @group libraries_provider
 */
class LibrariesProviderKernelTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'lp_library_test',
    'libraries_provider',
    'autoservices',
    'core_event_dispatcher',
    'hook_event_dispatcher',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setup();

    $this->installEntitySchema('library');
  }

  /**
   * Test the libraries that exist and the replacement of the values.
   */
  public function testManagedLibraries() {
    $this->assertSame(
      array_keys(\Drupal::service(LibrariesProviderManager::class)->getManagedLibraries()),
      ['lp_library_test__fontawesome', 'lp_library_test__fontawesomesvg']
    );
    $library = \Drupal::service(LibraryDiscoveryInterface::class)->getLibraryByName('lp_library_test', 'fontawesome');
    $this->assertNotEmpty($library['css']);
    $libraryEntity = Library::create([
      'id' => 'lp_library_test__fontawesome',
      'label' => 'fontawesome',
      // Disable.
      'enabled' => FALSE,
      // Change version.
      'version' => '5.14.0',
      'source' => 'cdn.jsdelivr.net',
      'minified' => 'when_aggregating',
      'variant' => '',
      'replaces' => [],
      'custom_options' => [],
    ]);
    $libraryEntity->save();

    $library = \Drupal::service(LibraryDiscoveryInterface::class)->getLibraryByName('lp_library_test', 'fontawesome');
    $this->assertEmpty($library['css']);
    $this->assertEquals($library['version'], $libraryEntity->get('version'));
  }

}
