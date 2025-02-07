<?php

declare(strict_types=1);

namespace Drupal\ui_patterns;

/**
 * Trait for sources handling enum values.
 */
trait ContextMatcherPluginManagerTrait {

  /**
   * The static cache.
   *
   * @var array<string, mixed>
   */
  protected array $staticCache = [];

  /**
   * Advanced method to get source definitions for contexts.
   *
   *  In addition to getDefinitionsForContexts(), this method
   *  checks context_definitions of plugins according to their keys.
   *  When required in def, a context must be present with same key,
   *  and it must satisfy the context definition.
   *
   * @param \Drupal\Core\Plugin\Context\ContextInterface[] $contexts
   *   Contexts.
   * @param array<string, bool> $tag_filter
   *   Filter results by tags.
   *     The array keys are the tags, and the values are boolean.
   *     If the value is TRUE, the tag is required.
   *     If the value is FALSE, the tag is forbidden.
   *
   * @return array<string, array<string, mixed> >
   *   Plugin definitions
   */
  public function getDefinitionsMatchingContextsAndTags(array $contexts = [], ?array $tag_filter = NULL) : array {
    $cacheKey = $this->getHashKey(__FUNCTION__, [$contexts, $tag_filter]);
    if (isset($this->staticCache[$cacheKey])) {
      return $this->staticCache[$cacheKey];
    }
    $definitions = $this->getDefinitionsMatchingContexts($contexts);
    if (is_array($tag_filter)) {
      $definitions = static::filterDefinitionsByTags($definitions, $tag_filter);
    }
    $this->staticCache[$cacheKey] = $definitions;
    return $definitions;
  }

  /**
   * Implementation of getDefinitionsMatchingContexts.
   *
   * @param \Drupal\Core\Plugin\Context\ContextInterface[] $contexts
   *   Contexts.
   *
   * @return array<string, array<string, mixed> >
   *   Plugin definitions
   */
  private function getDefinitionsMatchingContexts(array $contexts = []) : array {
    $definitions = $this->getDefinitionsForContexts($contexts);
    $checked_context_by_keys = [];
    foreach (array_keys($contexts) as $key) {
      $checked_context_by_keys[$key] = [];
    }
    $definitions = array_filter($definitions, function ($definition) use ($contexts, &$checked_context_by_keys) {
      $context_definitions = isset($definition['context_definitions']) ? $definition['context_definitions'] ?? [] : [];
      foreach ($context_definitions as $key => $context_definition) {
        if (!$context_definition->isRequired()) {
          continue;
        }
        if (!array_key_exists($key, $contexts)) {
          return FALSE;
        }
        $context_definition_key = hash('sha256', serialize($context_definition));
        if (!isset($checked_context_by_keys[$key][$context_definition_key])) {
          $checked_context_by_keys[$key][$context_definition_key] = $context_definition->isSatisfiedBy($contexts[$key]);
        }
        if (!$checked_context_by_keys[$key][$context_definition_key]) {
          return FALSE;
        }
      }
      return TRUE;
    });
    return $definitions;
  }

  /**
   * Filters definitions by tags.
   *
   * @param array $definitions
   *   The definitions.
   * @param array<string, bool> $tag_filter
   *   Filter results by tags.
   *    The array keys are the tags, and the values are boolean.
   *    If the value is TRUE, the tag is required.
   *    If the value is FALSE, the tag is forbidden.
   *
   * @return array
   *   The filtered definitions.
   */
  public static function filterDefinitionsByTags(array $definitions, array $tag_filter): array {
    return array_filter($definitions, static function ($definition) use ($tag_filter) {
      $tags = array_key_exists("tags", $definition) ? $definition['tags'] : [];
      if (count($tag_filter) > 0) {
        foreach ($tag_filter as $tag => $tag_required) {
          $found = in_array($tag, $tags);
          if (($tag_required && !$found) || (!$tag_required && $found)) {
            return FALSE;
          }
        }
      }
      return TRUE;
    });
  }

  /**
   * Get a hash key for caching.
   *
   * @param string $key
   *   A key.
   * @param array $contexts
   *   An array of contexts.
   *
   * @return string
   *   The hash key.
   */
  private function getHashKey(string $key, array $contexts = []) : string {
    return hash("sha256", serialize([$key, $contexts]));
  }

}
