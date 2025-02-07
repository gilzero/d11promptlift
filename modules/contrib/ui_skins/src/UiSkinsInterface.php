<?php

declare(strict_types=1);

namespace Drupal\ui_skins;

/**
 * Provides an interface for ui_skins constants.
 */
interface UiSkinsInterface {

  /**
   * The theme config key added for CSS variables.
   */
  public const CSS_VARIABLES_THEME_SETTING_KEY = 'ui_skins_css_variables';

  /**
   * The theme config key form theme.
   */
  public const THEME_THEME_SETTING_KEY = 'ui_skins_theme';

}
