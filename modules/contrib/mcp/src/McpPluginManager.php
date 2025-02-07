<?php

declare(strict_types=1);

namespace Drupal\mcp;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\mcp\Attribute\Mcp;

/**
 * Mcp plugin manager.
 */
final class McpPluginManager extends DefaultPluginManager {

  /**
   * Constructs the object.
   */
  public function __construct(
    \Traversable $namespaces,
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler,
    protected ConfigFactoryInterface $config_factory,
  ) {
    parent::__construct(
      'Plugin/Mcp', $namespaces, $module_handler, McpInterface::class,
      Mcp::class
    );
    $this->alterInfo('mcp_info');
    $this->setCacheBackend($cache_backend, 'mcp_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = []) {
    $config = $this->config_factory->get('mcp.settings');
    $plugin_config = $config->get("plugins.$plugin_id") ?? [];

    return parent::createInstance(
      $plugin_id,
      NestedArray::mergeDeep(
        $plugin_config,
        $configuration
      )
    );
  }

  /**
   * Get available plugins.
   *
   * @param bool $disabled
   *   Whether to include disabled plugins.
   *
   * @return \Drupal\mcp\McpInterface[]
   *   The available plugins.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *    If the plugin cannot be instantiated.
   */
  public function getAvailablePlugins(bool $disabled = FALSE): array {
    $definitions = $this->getDefinitions();
    $available_plugins = [];
    foreach ($definitions as $plugin_id => $definition) {
      if (!preg_match('/^[a-zA-Z0-9-]+$/', $plugin_id)) {
        throw new \InvalidArgumentException(
          'Plugin ID must be made of letters, numbers, and hyphens. Invalid plugin: '
          . $plugin_id
        );
      }

      $instance = $this->createInstance($plugin_id);

      if ($instance->checkRequirements()
        && ($disabled
          || $instance->isEnabled())
      ) {
        $available_plugins[$plugin_id] = $instance;
      }
    }

    return $available_plugins;
  }

}
