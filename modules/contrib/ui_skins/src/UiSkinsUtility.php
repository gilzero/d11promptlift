<?php

declare(strict_types=1);

namespace Drupal\ui_skins;

/**
 * Contains helper methods for UI Skins.
 */
class UiSkinsUtility {

  /**
   * The character used has placeholder for the dot character.
   *
   * Keyed lists in config cannot accept dot. So this character is used when
   * saving are reading the scopes in config.
   */
  public const DOT_CONVERSION_CHARACTER = '%';

  /**
   * Prepare a scope for CSS usage.
   *
   * Transform DOT_CONVERSION_CHARACTER into dot.
   *
   * @param string $scope
   *   The scope to convert.
   *
   * @return string
   *   The converted scope.
   */
  public static function getCssScopeName(string $scope): string {
    return \str_replace(self::DOT_CONVERSION_CHARACTER, '.', $scope);
  }

  /**
   * Prepare a scope for config storage.
   *
   * Transform dot into DOT_CONVERSION_CHARACTER.
   *
   * @param string $scope
   *   The scope to convert.
   *
   * @return string
   *   The converted scope.
   */
  public static function getConfigScopeName(string $scope): string {
    return \str_replace('.', self::DOT_CONVERSION_CHARACTER, $scope);
  }

  /**
   * Get CSS variable name.
   *
   * @param string $key
   *   The key.
   *
   * @return string
   *   The CSS variable name.
   */
  public static function getCssVariableName($key): string {
    $key = \strtr($key, ['_' => '-']);
    return "--{$key}";
  }

  /**
   * Prepare inline CSS from scope grouped CSS variables.
   *
   * @param array $cssVariables
   *   The CSS variables.
   *
   * @return string
   *   The inline CSS.
   */
  public static function getCssVariablesInlineCss(array $cssVariables): string {
    $inline_css = '';
    foreach ($cssVariables as $scope => $variables) {
      $scope_variables = [];
      foreach ($variables as $variable_name => $variable_value) {
        $scope_variables[] = "{$variable_name}:{$variable_value};";
      }
      $inline_css .= "{$scope}{" . \implode('', $scope_variables) . '}';
    }
    return $inline_css;
  }

}
