<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_suite_dsfr\Kernel;

use Drupal\Core\Extension\ExtensionVersion;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\ui_patterns\Traits\ConfigImporterTrait;
use Drupal\Tests\ui_patterns\Traits\TestContentCreationTrait;
use Drupal\Tests\ui_patterns\Traits\TestDataTrait;

/**
 * Base class for Kernel tests.
 */
class KernetTestBase extends KernelTestBase {

  use TestContentCreationTrait;
  use ConfigImporterTrait;
  use TestDataTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'text',
    'field',
    'node',
    'ui_patterns',
    'ui_patterns_test',
    'datetime',
    'filter',
    'inline_form_errors', 'ui_patterns', 'ui_styles', 'ui_skins', 'link',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installSchema('user', ['users_data']);
    $this->installSchema('node', 'node_access');
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installConfig(['system', 'filter']);

    $drupalVersion = ExtensionVersion::createFromVersionString(\Drupal::VERSION);
    if ((int) $drupalVersion->getMajorVersion() < 11 ||
      ($drupalVersion->getMajorVersion() === "11" && (int) $drupalVersion->getMinorVersion() < 1)) {
      $this->enableModules(['ui_icons_backport']);
    }
    $this->enableModules([
      'ui_icons', 'ui_icons_patterns',
    ]);
    \Drupal::service('theme_installer')->install([
      'ui_suite_dsfr',
    ]);
    $this->config('system.theme')->set('default', 'ui_suite_dsfr')->save();
    $this->createTestContentContentType();
  }

}
