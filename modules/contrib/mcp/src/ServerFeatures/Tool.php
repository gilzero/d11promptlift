<?php

namespace Drupal\mcp\ServerFeatures;

/**
 * Tool Class.
 *
 * Represents a tool that can be executed.
 */
class Tool implements ToolInterface {

  public function __construct(
    string $name,
    string $description,
    mixed $inputSchema,
  ) {
    $this->name = $name;
    $this->description = $description;
    $this->inputSchema = $inputSchema;
  }

  /**
   * Tool name.
   */
  public string $name;

  /**
   * Tool Description.
   */
  public string $description;

  /**
   * Input Schema definition.
   */
  public mixed $inputSchema;

}
