<?php

namespace Drupal\mcp\ServerFeatures;

/**
 * Resource Class.
 *
 * Represents a resource that can be used.
 */
class Resource implements ResourceInterface {

  public function __construct(
    string $uri,
    ?string $name,
    ?string $description,
    ?string $mimeType,
    ?string $text,
  ) {
    $this->uri = $uri;
    $this->name = $name;
    $this->description = $description;
    $this->mimeType = $mimeType;
    $this->text = $text;
  }

  /**
   * Resource URI.
   */
  public string $uri;

  /**
   * Resource name.
   */
  public ?string $name;

  /**
   * Resource Description.
   */
  public ?string $description;

  /**
   * Resource MimeType.
   */
  public ?string $mimeType;

  /**
   * Resource Text.
   */
  public ?string $text;

}
