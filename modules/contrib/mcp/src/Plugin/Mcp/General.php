<?php

declare(strict_types=1);

namespace Drupal\mcp\Plugin\Mcp;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\mcp\Attribute\Mcp;
use Drupal\mcp\McpPluginBase;
use Drupal\mcp\ServerFeatures\Tool;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the mcp.
 */
#[Mcp(
  id: 'general',
  name: new TranslatableMarkup('General MCP'),
  description: new TranslatableMarkup(
    'Provides general MCP functionality and basic tools.'
  ),
)]
final class General extends McpPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritDoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
  ) {
    $instance = new General(
      $configuration,
      $plugin_id,
      $plugin_definition,
    );

    $instance->configFactory = $container->get('config.factory');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getTools(): array {
    return [
      new Tool(
        name: "info",
        description: 'Returns information about the site.',
        inputSchema: [
          'type'       => 'object',
          'properties' => new \stdClass(),
        ]
      ),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function executeTool(string $toolId, mixed $arguments): array {
    if ($toolId === 'info') {
      return [
        [
          "type" => "text",
          "text" => json_encode([
            'siteName'   => $this->configFactory->get('system.site')->get(
              'name'
            ),
            'siteSlogan' => $this->configFactory->get('system.site')->get(
              'slogan'
            ),
            'version'    => \Drupal::VERSION,
          ]),
        ],
      ];
    }

    throw new \InvalidArgumentException('Tool not found');
  }

}
