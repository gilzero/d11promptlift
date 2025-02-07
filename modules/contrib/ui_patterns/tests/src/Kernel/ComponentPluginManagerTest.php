<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_patterns\Kernel;

use Drupal\Core\Theme\ComponentPluginManager;
use Drupal\KernelTests\KernelTestBase;

/**
 * Test the ComponentPluginManager service.
 *
 * @coversDefaultClass \Drupal\ui_patterns\ComponentPluginManager
 *
 * @group ui_patterns
 */
final class ComponentPluginManagerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['ui_patterns', 'ui_patterns_test'];

  /**
   * Themes to install.
   *
   * @var string[]
   */
  protected static $themes = [];

  /**
   * The component plugin manager from ui_patterns.
   *
   * @var \Drupal\Core\Theme\ComponentPluginManager
   */
  protected ComponentPluginManager $manager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->manager = \Drupal::service('plugin.manager.sdc');
  }

  /**
   * Test the method hook_component_info_alter().
   */
  public function testHookComponentInfoAlter() : void {
    $definition = $this->manager->getDefinition('ui_patterns_test:test-component');
    $this->assertEquals('Hook altered', $definition['variants']['hook']['title']);
  }

  /**
   * Test the method ::getCategories().
   */
  public function testGetCategories() : void {
    /* @phpstan-ignore method.notFound */
    $categories = $this->manager->getCategories();
    $this->assertNotEmpty($categories);
  }

  /**
   * Test the method ::getSortedDefinitions().
   */
  public function testGetSortedDefinitions(): void {
    /* @phpstan-ignore method.notFound */
    $sortedDefinitions = $this->manager->getSortedDefinitions();
    $this->assertNotEmpty($sortedDefinitions);
  }

  /**
   * Test the method ::getGroupedDefinitions().
   */
  public function testGetGroupedDefinitions(): void {
    /* @phpstan-ignore method.notFound */
    $groupedDefinitions = $this->manager->getGroupedDefinitions();
    $this->assertNotEmpty($groupedDefinitions);
  }

}
