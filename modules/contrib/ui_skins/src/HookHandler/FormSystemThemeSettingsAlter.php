<?php

declare(strict_types=1);

namespace Drupal\ui_skins\HookHandler;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ui_skins\Theme\ThemePluginManagerInterface;
use Drupal\ui_skins\UiSkinsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Alter theme settings form.
 */
class FormSystemThemeSettingsAlter implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The theme plugin manger.
   *
   * @var \Drupal\ui_skins\Theme\ThemePluginManagerInterface
   */
  protected ThemePluginManagerInterface $themePluginManager;

  /**
   * Constructor.
   *
   * @param \Drupal\ui_skins\Theme\ThemePluginManagerInterface $themePluginManager
   *   The themes plugin manager.
   */
  public function __construct(ThemePluginManagerInterface $themePluginManager) {
    $this->themePluginManager = $themePluginManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('plugin.manager.ui_skins.theme')
    );
  }

  /**
   * Add theme form in system theme settings.
   */
  public function alter(array &$form, FormStateInterface $form_state): void {
    $form_theme_name = '';
    // Extract theme name from $form.
    if (isset($form['config_key']['#value']) && \is_string($form['config_key']['#value'])) {
      $config_key = $form['config_key']['#value'];
      $config_key_parts = \explode('.', $config_key);

      if (isset($config_key_parts[0])) {
        $form_theme_name = $config_key_parts[0];
      }
    }
    // Impossible to determine on which theme settings form we are.
    if (empty($form_theme_name)) {
      return;
    }

    $plugin_definitions = $this->themePluginManager->getDefinitionsForTheme($form_theme_name);
    if (empty($plugin_definitions)) {
      return;
    }

    $options = [];
    foreach ($plugin_definitions as $plugin_definition) {
      $options[$plugin_definition->id()] = $plugin_definition->getLabel();
    }
    $form[UiSkinsInterface::THEME_THEME_SETTING_KEY] = [
      '#type' => 'select',
      '#title' => $this->t('Theme'),
      '#options' => $options,
      '#empty_option' => $this->t('Select theme'),
      '#default_value' => \theme_get_setting(UiSkinsInterface::THEME_THEME_SETTING_KEY, $form_theme_name) ?? '',
    ];
  }

}
