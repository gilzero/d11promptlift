<?php

declare(strict_types=1);

namespace Drupal\ui_skins\HookHandler;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\ui_skins\UiSkinsInterface;
use Drupal\ui_skins\UiSkinsUtility;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Inject inline CSS.
 */
class PageTop implements ContainerInjectionInterface {

  /**
   * The theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected ThemeManagerInterface $themeManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Theme\ThemeManagerInterface $themeManager
   *   The theme manager.
   */
  public function __construct(ThemeManagerInterface $themeManager) {
    $this->themeManager = $themeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('theme.manager')
    );
  }

  /**
   * The page top key for CSS variables.
   */
  public const PAGE_TOP_CSS_VARIABLES_KEY = 'ui_skins_css_variables';

  /**
   * Inject inline CSS variables.
   *
   * @param array $page_top
   *   The page top elements.
   */
  public function alter(array &$page_top): void {
    $ui_skins_css_variables_settings = \theme_get_setting(UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY);
    if (!\is_array($ui_skins_css_variables_settings)) {
      return;
    }

    // Prepare list of variables grouped by scope.
    $css_variables = [];
    foreach ($ui_skins_css_variables_settings as $plugin_id => $scoped_values) {
      $variable_name = UiSkinsUtility::getCssVariableName($plugin_id);
      foreach ($scoped_values as $scope => $value) {
        $css_variables = NestedArray::mergeDeep($css_variables, [
          UiSkinsUtility::getCssScopeName($scope) => [
            $variable_name => $value,
          ],
        ]);
      }
    }

    if (empty($css_variables)) {
      return;
    }

    $page_top[static::PAGE_TOP_CSS_VARIABLES_KEY] = [
      '#type' => 'html_tag',
      '#tag' => 'style',
      '#value' => UiSkinsUtility::getCssVariablesInlineCss($css_variables),
      '#cache' => [
        'tags' => [
          'config:' . $this->themeManager->getActiveTheme()->getName() . '.settings',
        ],
      ],
    ];
  }

}
