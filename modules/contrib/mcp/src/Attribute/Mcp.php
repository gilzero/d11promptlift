<?php

declare(strict_types=1);

namespace Drupal\mcp\Attribute;

use Drupal\Component\Plugin\Attribute\AttributeBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The mcp attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class Mcp extends AttributeBase {

  /**
   * Constructs a new Mcp instance.
   *
   * @param string $id
   *   The plugin ID. There are some implementation bugs that make the plugin
   *   available only if the ID follows a specific pattern. It must be either
   *   identical to group or prefixed with the group. E.g. if the group is "foo"
   *   the ID must be either "foo" or "foo:bar".
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $name
   *   Human readable name of the plugin.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $description
   *   Description of what the plugin does.
   */
  public function __construct(
    public readonly string $id,
    public readonly TranslatableMarkup $name,
    public readonly TranslatableMarkup $description,
  ) {}

}
