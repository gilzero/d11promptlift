<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_skins\Unit;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\ui_skins\Definition\ThemeDefinition;
use Drupal\ui_skins\Theme\ThemePluginManager;
use Drupal\ui_skins_test\DummyThemePluginManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Test the CSS variable plugin manager.
 *
 * @group ui_skins
 *
 * @coversDefaultClass \Drupal\ui_skins\Theme\ThemePluginManager
 */
class ThemePluginManagerTest extends UnitTestCase {

  /**
   * The container.
   *
   * @var \Symfony\Component\DependencyInjection\TaggedContainerInterface
   */
  protected $container;

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected TranslationInterface $stringTranslation;

  /**
   * The themes plugin manager.
   *
   * @var \Drupal\ui_skins_test\DummyThemePluginManager
   */
  protected DummyThemePluginManager $themePluginManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->container = new ContainerBuilder();
    $this->container->set('string_translation', $this->getStringTranslationStub());

    // Set up for this class.
    /** @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit\Framework\MockObject\MockObject $moduleHandler */
    $moduleHandler = $this->createMock(ModuleHandlerInterface::class);
    $moduleHandler->expects($this->any())
      ->method('getModuleDirectories')
      ->willReturn([]);

    /** @var \Drupal\Core\Extension\ThemeHandlerInterface|\PHPUnit\Framework\MockObject\MockObject $themeHandler */
    $themeHandler = $this->createMock(ThemeHandlerInterface::class);
    $themeHandler->expects($this->any())
      ->method('getThemeDirectories')
      ->willReturn([]);

    $cache = $this->createMock(CacheBackendInterface::class);

    $this->themePluginManager = new DummyThemePluginManager($cache, $moduleHandler, $themeHandler);
  }

  /**
   * Tests the constructor.
   *
   * @covers ::__construct
   */
  public function testConstructor(): void {
    $this->assertInstanceOf(
      ThemePluginManager::class,
      $this->themePluginManager
    );
  }

  /**
   * Tests the processDefinition().
   *
   * @covers ::processDefinition
   */
  public function testProcessDefinitionWillReturnException(): void {
    $plugin_id = 'test';
    $definition = ['no_id' => $plugin_id];
    try {
      $this->themePluginManager->processDefinition($definition, $plugin_id);
    }
    catch (PluginException $exception) {
      $this->assertTrue(TRUE, 'The expected exception happened.');
    }
  }

  /**
   * Tests the processDefinition().
   *
   * @covers ::processDefinition
   */
  public function testProcessDefinition(): void {
    $plugin_id = 'test';
    $definition = ['id' => $plugin_id];

    $expected = new ThemeDefinition($definition);

    /** @var \Drupal\ui_skins\Definition\ThemeDefinition $definition */
    $this->themePluginManager->processDefinition($definition, $plugin_id);
    $this->assertInstanceOf(ThemeDefinition::class, $definition);
    $this->assertEquals($definition->toArray(), $expected->toArray());
  }

  /**
   * Tests the getDefinitionWithDependencies().
   *
   * @covers ::getDefinitionWithDependencies
   */
  public function testGetDefinitionWithDependencies(): void {
    $themes = [
      'no_dependencies' => [
        'id' => 'no_dependencies',
        'label' => 'No dependencies',
        'value' => 'foo',
      ],
      'with_dependencies' => [
        'id' => 'with_dependencies',
        'label' => 'With dependencies',
        'value' => 'foo',
        'dependencies' => [
          'dependency_1',
          'dependency_2',
        ],
      ],
      'dependency_1' => [
        'id' => 'dependency_1',
        'label' => 'Dependency 1',
        'value' => 'foo',
        'dependencies' => [
          'dependency_1_1',
        ],
      ],
      'dependency_1_1' => [
        'id' => 'dependency_1_1',
        'label' => 'Dependency 1 1',
        'value' => 'foo',
      ],
      'dependency_2' => [
        'id' => 'dependency_2',
        'label' => 'Dependency 2',
        'value' => 'foo',
      ],
    ];
    $this->themePluginManager->setThemes($themes);

    $definitions = $this->themePluginManager->getDefinitionWithDependencies('no_dependencies');
    $this->assertCount(1, $definitions);
    $this->assertInstanceOf(ThemeDefinition::class, $definitions[0]);
    $this->assertSame('No dependencies', $definitions[0]->getLabel());

    $definitions = $this->themePluginManager->getDefinitionWithDependencies('dependency_1');
    $this->assertCount(2, $definitions);
    $this->assertInstanceOf(ThemeDefinition::class, $definitions[0]);
    $this->assertSame('Dependency 1 1', $definitions[0]->getLabel());
    $this->assertInstanceOf(ThemeDefinition::class, $definitions[1]);
    $this->assertSame('Dependency 1', $definitions[1]->getLabel());

    $definitions = $this->themePluginManager->getDefinitionWithDependencies('with_dependencies');
    $this->assertCount(4, $definitions);
    $this->assertInstanceOf(ThemeDefinition::class, $definitions[0]);
    $this->assertSame('Dependency 1 1', $definitions[0]->getLabel());
    $this->assertInstanceOf(ThemeDefinition::class, $definitions[1]);
    $this->assertSame('Dependency 1', $definitions[1]->getLabel());
    $this->assertInstanceOf(ThemeDefinition::class, $definitions[2]);
    $this->assertSame('Dependency 2', $definitions[2]->getLabel());
    $this->assertInstanceOf(ThemeDefinition::class, $definitions[3]);
    $this->assertSame('With dependencies', $definitions[3]->getLabel());
  }

}
