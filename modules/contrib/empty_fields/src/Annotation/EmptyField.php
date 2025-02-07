<?php

namespace Drupal\empty_fields\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a empty field annotation object.
 *
 * Empty field plugins provides render replacement for fields without value.
 *
 * Plugin Namespace: Plugin\EmptyFields
 *
 * @see \Drupal\empty_fields\EmptyFieldPluginBase
 * @see \Drupal\empty_fields\EmptyFieldPluginInterface
 * @see \Drupal\empty_fields\EmptyFieldsPluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class EmptyField extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

}
