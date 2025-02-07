<?php

namespace Drupal\mcp_ai\Plugin\Mcp;

use Drupal\ai\OperationType\Chat\Tools\ToolsFunctionOutput;
use Drupal\ai\Service\FunctionCalling\ExecutableFunctionCallInterface;
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
  id: 'ai-function-calling',
  name: new TranslatableMarkup('AI Function Calling'),
  description: new TranslatableMarkup(
    'Provides AI function calling capabilities through the MCP protocol.'
  ),
)]
class AiFunctionCalling extends McpPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The AI function calls plugin manager.
   *
   * @var ?\Drupal\ai\Service\FunctionCalling\FunctionCallPluginManager
   */
  protected $aiFunctionCalls;

  /**
   * {@inheritDoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
  ) {
    $instance = new AiFunctionCalling(
      $configuration,
      $plugin_id,
      $plugin_definition,
    );

    $instance->aiFunctionCalls = $container->get(
      'ai.function_calls',
      ContainerInterface::NULL_ON_INVALID_REFERENCE
    );

    return $instance;
  }

  /**
   * {@inheritDoc}
   */
  public function checkRequirements(): bool {
    return $this->aiFunctionCalls !== NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function getTools(): array {
    $functionCallDefinitions = $this->aiFunctionCalls->getDefinitions();
    $tools = [];
    foreach ($functionCallDefinitions as $functionCallDefinition) {
      $instance = $this->aiFunctionCalls->createInstance(
        $functionCallDefinition['id']
      );
      $normalized = $instance->normalize();
      $converted = $normalized->renderFunctionArray();

      $tools[] = new Tool(
        name: "{$instance->getFunctionName()}",
        description: $converted['description'],
        inputSchema: $converted['parameters'],
      );
    }

    return $tools;
  }

  /**
   * {@inheritDoc}
   */
  public function executeTool(string $toolId, mixed $arguments): array {
    $definitions = $this->aiFunctionCalls->getDefinitions();
    $instances = [];
    foreach ($definitions as $definition) {
      $instance = $this->aiFunctionCalls->createInstance($definition['id']);
      if (!$instance instanceof ExecutableFunctionCallInterface) {
        continue;
      }
      if ($instance->getFunctionName() === $toolId) {
        $instances[] = $instance;
      }
    }

    if (empty($instances)) {
      return [];
    }

    $results = [];
    foreach ($instances as $instance) {
      $input = new ToolsFunctionOutput(
        input: $instance->normalize(),
        arguments: $arguments,
      );
      $input->validate();
      $instance->populateValues($input);
      $instance->execute();

      $results[]
        = [
          "type" => "text",
          "text" => $instance->getReadableOutput(),
        ];
    }

    return $results;
  }

}
