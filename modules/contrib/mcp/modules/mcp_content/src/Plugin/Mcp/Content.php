<?php

namespace Drupal\mcp_content\Plugin\Mcp;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\mcp\Attribute\Mcp;
use Drupal\mcp\McpPluginBase;
use Drupal\mcp\ServerFeatures\Resource;
use Drupal\mcp\ServerFeatures\ResourceTemplate;
use Drupal\mcp\ServerFeatures\Tool;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the mcp.
 */
#[Mcp(
  id: 'content',
  name: new TranslatableMarkup('Content'),
  description: new TranslatableMarkup(
    'Provides MCP integration with Drupal content and fields.'
  ),
)]
final class Content extends McpPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * The entity type manager.
   *
   * @var ?\Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var ?\Drupal\Core\Entity\EntityFieldManagerInterface
   */
  private $entityFieldManager;

  /**
   * {@inheritDoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
  ) {
    $instance = new Content(
      $configuration,
      $plugin_id,
      $plugin_definition,
    );

    $instance->moduleHandler = $container->get('module_handler');
    $instance->entityTypeManager = $container->get(
      'entity_type.manager', ContainerInterface::NULL_ON_INVALID_REFERENCE
    );
    $instance->entityFieldManager = $container->get(
      'entity_field.manager', ContainerInterface::NULL_ON_INVALID_REFERENCE
    );

    return $instance;
  }

  /**
   * {@inheritDoc}
   */
  public function checkRequirements(): bool {
    return $this->moduleHandler->moduleExists('node');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $config = parent::defaultConfiguration();

    // Get all available content types and enable them by default.
    $nodeTypes = $this->entityTypeManager->getStorage('node_type')
      ->loadMultiple();

    $config['config']['content_types'] = [];
    foreach ($nodeTypes as $nodeType) {
      $config['config']['content_types'][$nodeType->id()] = TRUE;
    }

    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(
    array $form,
    FormStateInterface $form_state,
  ): array {
    $config = $this->getConfiguration();
    $nodeTypes = $this->entityTypeManager->getStorage('node_type')
      ->loadMultiple();

    $form['content_types'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];

    foreach ($nodeTypes as $nodeType) {
      $typeId = $nodeType->id();
      $form['content_types'][$typeId] = [
        '#type'          => 'checkbox',
        '#title'         => $nodeType->label(),
        '#description'   => $nodeType->getDescription(),
        '#default_value' => $config['config']['content_types'][$typeId] ?? TRUE,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getResources(): array {
    $nodeTypes = $this->entityTypeManager->getStorage('node_type')
      ->loadMultiple();
    $resources = [];

    foreach ($nodeTypes as $nodeType) {
      if (!$this->isContentTypeEnabled($nodeType->id())) {
        continue;
      }

      $resources[] = new Resource(
        uri: "node/{$nodeType->id()}",
        name: $nodeType->label(),
        description: $nodeType->getDescription(),
        mimeType: 'application/json',
        text: NULL,
      );
    }

    return $resources;
  }

  /**
   * {@inheritdoc}
   */
  public function getResourceTemplates(): array {
    $nodeTypes = $this->entityTypeManager->getStorage('node_type')
      ->loadMultiple();
    $resourceTemplates = [];

    foreach ($nodeTypes as $nodeType) {
      if (!$this->isContentTypeEnabled($nodeType->id())) {
        continue;
      }

      $resourceTemplates[] = new ResourceTemplate(
        uriTemplate: "node/{$nodeType->id()}/{id}",
        name: $nodeType->label(),
        description: $nodeType->getDescription(),
        mimeType: 'application/json',
      );
    }

    return $resourceTemplates;
  }

  /**
   * {@inheritdoc}
   */
  public function readResource(string $resourceId): array {
    $parts = explode('/', $resourceId);

    if (count($parts) < 2 || $parts[0] !== 'node') {
      throw new \InvalidArgumentException(
        "Invalid resource ID format: $resourceId"
      );
    }

    if (!$this->isContentTypeEnabled($parts[1])) {
      throw new \InvalidArgumentException("Unknown resource Id: $resourceId");
    }

    if (count($parts) === 2) {
      return $this->readContentTypeInfo($parts[1]);
    }

    if (count($parts) === 3) {
      return $this->readNodeContent($parts[1], $parts[2]);
    }

    throw new \InvalidArgumentException("Unknown resource Id: $resourceId");
  }

  /**
   * Read and return node content.
   */
  private function readNodeContent(string $contentType, string $nodeId): array {
    $nodes = $this->entityTypeManager->getStorage('node')
      ->loadByProperties([
        'nid'  => $nodeId,
        'type' => $contentType,
      ]);
    $node = reset($nodes);

    if (!$node instanceof NodeInterface) {
      throw new \InvalidArgumentException("Node not found: $nodeId");
    }
    $fieldDefinitions = $this->entityFieldManager->getFieldDefinitions(
      'node', $contentType
    );

    $nodeData = [];
    foreach ($fieldDefinitions as $fieldName => $definition) {
      if ($node->hasField($fieldName) && !$node->get($fieldName)->isEmpty()
        && $this->isSupportedField($definition)
      ) {
        $field = $node->get($fieldName);
        $nodeData[$fieldName] = $definition->getFieldStorageDefinition()
          ->isMultiple() ?
          array_map(
            fn($item) => $item['value'], $field->getValue()
          ) : $field->getString();
      }
    }

    return [
      new Resource(
        uri: "node/$contentType/$nodeId",
        name: $node->getTitle(),
        description: NULL,
        mimeType: 'application/json',
        text: json_encode(
          $nodeData, JSON_UNESCAPED_UNICODE
        ),
      ),
    ];
  }

  /**
   * Read and return content type information.
   */
  private function readContentTypeInfo(string $contentType): array {
    $nodeType = $this->entityTypeManager->getStorage('node_type')
      ->load($contentType);

    if (!$nodeType) {
      throw new \InvalidArgumentException(
        "Content type not found: $contentType"
      );
    }

    $fieldDefinitions = $this->entityFieldManager->getFieldDefinitions(
      'node', $contentType
    );
    $fields = [];
    foreach ($fieldDefinitions as $fieldName => $definition) {
      // @todo This is temporary solution and need to be refactored.
      // Expose only title, body and user defined fields that are supported.
      if (!$this->isSupportedField($definition)) {
        continue;
      }

      $fields[$fieldName] = [
        'name'        => $definition->getLabel(),
        'type'        => $definition->getType(),
        'description' => $definition->getDescription(),
        'required'    => $definition->isRequired(),
        'multiple'    => $definition->getFieldStorageDefinition()->isMultiple(),
      ];
    }

    return [
      new Resource(
        uri: "node/$contentType",
        name: 'Content type info for ' . $nodeType->label(),
        description: "Fields and properties for content type $contentType. This is only available and supported fields.",
        mimeType: 'application/json',
        text: json_encode([
          'name'        => $nodeType->label(),
          'id'          => $nodeType->id(),
          'description' => $nodeType->getDescription(),
          'fields'      => $fields,
        ], JSON_UNESCAPED_UNICODE),
      ),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getTools(): array {
    return [
      new Tool(
        name: "search-content",
        description: 'Search content using filters. Multiple filters are combined with AND logic.',
        inputSchema: [
          'type'       => 'object',
          'properties' => [
            'contentType' => [
              'type'        => 'string',
              'description' => 'Content type machine name (e.g., article, page)',
            ],
            'filters'     => [
              'type'        => 'array',
              'description' => 'Array of filter conditions',
              'items'       => [
                'type'       => 'object',
                'properties' => [
                  'field'    => [
                    'type'        => 'string',
                    'description' => 'Field name to filter on',
                  ],
                  'value'    => [
                    'type'        => ['string', 'number', 'array'],
                    'description' => 'Value to filter by',
                  ],
                  'operator' => [
                    'type'        => 'string',
                    'description' => 'Comparison operator',
                    'enum'        => [
                      '=',
                      '<>',
                      '>',
                      '>=',
                      '<',
                      '<=',
                      'CONTAINS',
                      'STARTS_WITH',
                      'ENDS_WITH',
                      'IN',
                      'NOT IN',
                      'BETWEEN',
                      'IS NULL',
                      'IS NOT NULL',
                    ],
                    'default'     => '=',
                  ],
                ],
                'required'   => ['field', 'value'],
              ],
            ],
            'limit'       => [
              'type'        => 'integer',
              'description' => 'Maximum number of results',
              'default'     => 10,
            ],
            'offset'      => [
              'type'        => 'integer',
              'description' => 'Starting point for pagination',
              'default'     => 0,
            ],
            'sort'        => [
              'type'        => 'object',
              'description' => 'Sort options',
              'properties'  => [
                'field' => [
                  'type'        => 'string',
                  'description' => 'Field name to sort by',
                ],
                'order' => [
                  'type'        => 'string',
                  'description' => 'Sort order',
                  'enum'        => ['ASC', 'DESC'],
                  'default'     => 'ASC',
                ],
              ],
              'required'    => ['field'],
            ],
          ],
          'required'   => ['contentType', 'filters'],
        ],
      ),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function executeTool(string $toolId, mixed $arguments): array {
    if ($toolId === 'search-content') {
      return $this->searchContent($arguments);
    }

    return [];
  }

  /**
   * Execute content search based on provided filters.
   */
  public function searchContent(array $arguments): array {
    $contentType = $arguments['contentType'];
    if (!is_string($contentType)) {
      throw new \InvalidArgumentException('Content type must be a string');
    }

    $filters = $arguments['filters'];
    if (!is_array($filters)) {
      throw new \InvalidArgumentException('Filters must be an array');
    }

    if (!$this->isContentTypeEnabled($contentType)) {
      throw new \InvalidArgumentException("Unknown content type: $contentType");
    }

    $limit = $arguments['limit'] ?? 10;
    $offset = $arguments['offset'] ?? 0;

    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', $contentType)
      ->condition('status', 1)
      ->range($offset, $limit)
      ->sort('created', 'DESC');

    $fieldDefinitions = $this->entityFieldManager->getFieldDefinitions(
      'node', $contentType
    );

    foreach ($filters as $filter) {
      $field = $filter['field'];

      if (!isset($fieldDefinitions[$field])
        || !$this->isSupportedField(
          $fieldDefinitions[$field]
        )
      ) {
        throw new \InvalidArgumentException("Unknown field: $field");
      }

      $value = $filter['value'];
      $operator = $filter['operator'] ?? '=';

      switch ($operator) {
        case 'CONTAINS':
          $query->condition($field, '%' . $value . '%', 'LIKE');
          break;

        case 'STARTS_WITH':
          $query->condition($field, $value . '%', 'LIKE');
          break;

        case 'ENDS_WITH':
          $query->condition($field, '%' . $value, 'LIKE');
          break;

        case 'IN':
          $query->condition($field, (array) $value, 'IN');
          break;

        case 'NOT IN':
          $query->condition($field, (array) $value, 'NOT IN');
          break;

        case 'BETWEEN':
          if (!is_array($value) || count($value) !== 2) {
            throw new \InvalidArgumentException(
              'BETWEEN operator requires an array with exactly 2 values'
            );
          }
          $query->condition($field, $value, 'BETWEEN');
          break;

        case 'IS NULL':
          $query->notExists($field);
          break;

        case 'IS NOT NULL':
          $query->exists($field);
          break;

        default:
          $query->condition($field, $value, $operator);
      }
    }

    if (isset($arguments['sort'])) {
      $sort = $arguments['sort'];
      if (!isset($fieldDefinitions[$sort['field']])
        || !$this->isSupportedField(
          $fieldDefinitions[$sort['field']]
        )
      ) {
        throw new \InvalidArgumentException("Unknown field: $sort[field]");
      }

      $query->sort($sort['field'], $sort['order'] === 'ASC' ? 'ASC' : 'DESC');
    }

    $nids = $query->execute();
    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

    $resources = [];
    foreach ($nodes as $node) {
      $resources[] = [
        "type"     => "resource",
        'resource' => $this->readNodeContent($contentType, $node->id())[0],
      ];
    }

    return $resources;
  }

  /**
   * Check if content type is enabled in configuration.
   *
   * @param string $contentType
   *   The content type machine name.
   */
  private function isContentTypeEnabled(string $contentType): bool {
    return $this->getConfiguration()['config']['content_types'][$contentType] ??
      TRUE;
  }

  /**
   * Check if field type is supported.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $fieldDefinition
   *   The field type.
   *
   * @return bool
   *   TRUE if field type is supported, FALSE otherwise.
   */
  private function isSupportedField(
    FieldDefinitionInterface $fieldDefinition,
  ): bool {
    $supportedTypes = [
      'string',
      'string_long',
      'list_string',
      'datetime',
      'boolean',
      'text_long',
    ];
    $fieldName = $fieldDefinition->getName();

    if (in_array($fieldName, ['title', 'body'])) {
      return TRUE;
    }

    if (str_starts_with($fieldName, 'field_')
      && in_array(
        $fieldDefinition->getType(), $supportedTypes
      )
    ) {
      return TRUE;
    }

    return FALSE;
  }

}
