<?php

declare(strict_types=1);

namespace Drupal\mcp;

use Drupal\mcp\ServerFeatures\Resource;
use Drupal\mcp\ServerFeatures\ResourceTemplate;
use Drupal\mcp\ServerFeatures\Tool;

/**
 * Service for MCP module.
 */
final class McpService {

  /**
   * Constructs a McpService object.
   */
  public function __construct(
    private readonly McpPluginManager $pluginManagerMcp,
  ) {}

  /**
   * Get tools.
   */
  public function getTools(): array {
    $tools = [];
    foreach ($this->pluginManagerMcp->getAvailablePlugins() as $instance) {
      $instanceTools = $instance->getTools();
      $prefixizedTools = array_map(
        fn($tool) => new Tool(
          name: $instance->getPluginId() . '_' . $tool->name,
          description: $tool->description,
          inputSchema: $tool->inputSchema,
        ),
        $instanceTools
      );
      $tools = array_merge($tools, $prefixizedTools);
    }

    return $tools;
  }

  /**
   * Execute a tool.
   */
  public function executeTool(string $toolId, mixed $arguments): array {
    [$definitionId, $pluginId] = explode('_', $toolId);
    $instance = $this->pluginManagerMcp->createInstance($definitionId);
    if (!$instance instanceof McpInterface) {
      return [];
    }

    if (!$instance->checkRequirements() || !$instance->isEnabled()) {
      return [];
    }

    $toolResult = $instance->executeTool($pluginId, $arguments);

    if (empty($toolResult)) {
      return [];
    }

    $prefixizedToolResult = [];
    foreach ($toolResult as $result) {
      $type = $result['type'];
      if (!empty($type) && !in_array($type, ['text', 'image', 'resource'])) {
        throw new \InvalidArgumentException('Invalid result type: ' . $type);
      }
      if ($type === 'resource' && $result instanceof Resource) {
        $prefixizedToolResult[] = new Resource(
          uri: $instance->getPluginId() . '://' . $result->uri,
          name: $result->name,
          description: $result->description,
          mimeType: $result->mimeType,
          text: $result->text,
        );
      }

      $prefixizedToolResult[] = $result;
    }

    return $prefixizedToolResult;
  }

  /**
   * Get resources.
   */
  public function getResources(): array {
    $resources = [];
    foreach ($this->pluginManagerMcp->getAvailablePlugins() as $instance) {
      $instanceResources = $instance->getResources();
      $prefixizedResources = array_map(
        fn($resource) => new Resource(
          uri: $instance->getPluginId() . '://' . $resource->uri,
          name: $resource->name,
          description: $resource->description,
          mimeType: $resource->mimeType,
          text: $resource->text,
        ),
        $instanceResources
      );

      $resources = array_merge($resources, $prefixizedResources);
    }

    return $resources;
  }

  /**
   * Get resource templates.
   */
  public function getResourceTemplates(): array {
    $resourceTemplates = [];
    foreach ($this->pluginManagerMcp->getAvailablePlugins() as $instance) {
      $instanceResourceTemplates = $instance->getResourceTemplates();
      $prefixizedResourceTemplates = array_map(
        fn($resourceTemplate) => new ResourceTemplate(
          uriTemplate: $instance->getPluginId() . '://'
          . $resourceTemplate->uriTemplate,
          name: $resourceTemplate->name,
          description: $resourceTemplate->description,
          mimeType: $resourceTemplate->mimeType,
        ),
        $instanceResourceTemplates
      );

      $resourceTemplates = array_merge(
        $resourceTemplates, $prefixizedResourceTemplates
      );
    }

    return $resourceTemplates;
  }

  /**
   * Read a resource.
   */
  public function readResource(string $resourceUri): array {
    // Resource URI format: pluginId://resourceId.
    [$pluginId, $resourceId] = explode('://', $resourceUri);
    $instance = $this->pluginManagerMcp->createInstance($pluginId);

    if (!$instance instanceof McpInterface) {
      return [];
    }

    if (!$instance->checkRequirements() || !$instance->isEnabled()) {
      return [];
    }

    $resources = $instance->readResource($resourceId);
    if (empty($resources)) {
      return [];
    }

    $prefixizedResources = [];
    foreach ($resources as $resource) {
      if (!$resource instanceof Resource) {
        continue;
      }

      $prefixizedResources[] = new Resource(
        uri: $pluginId . '://' . $resource->uri,
        name: $resource->name,
        description: $resource->description,
        mimeType: $resource->mimeType,
        text: $resource->text,
      );
    }

    return $prefixizedResources;
  }

}
