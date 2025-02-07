<?php

namespace Drupal\mcp\ServerFeatures;

/**
 * Resource Template Class.
 *
 * Represents a resource template that can be used.
 */
class ResourceTemplate implements ResourceTemplateInterface {

  public function __construct(
    string $uriTemplate,
    string $name,
    ?string $description,
    ?string $mimeType,
  ) {
    $this->uriTemplate = $uriTemplate;
    $this->name = $name;
    $this->description = $description;
    $this->mimeType = $mimeType;
  }

  /**
   * Resource URI template.
   */
  public string $uriTemplate;

  /**
   * Resource name.
   */
  public string $name;

  /**
   * Resource Description.
   */
  public ?string $description;

  /**
   * Resource MimeType.
   */
  public ?string $mimeType;

}
