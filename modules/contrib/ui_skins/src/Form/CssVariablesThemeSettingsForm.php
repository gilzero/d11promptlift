<?php

declare(strict_types=1);

namespace Drupal\ui_skins\Form;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\ui_skins\CssVariable\CssVariablePluginManagerInterface;
use Drupal\ui_skins\Definition\CssVariableDefinition;
use Drupal\ui_skins\UiSkinsInterface;
use Drupal\ui_skins\UiSkinsUtility;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * UI skins CSS variables theme settings.
 */
class CssVariablesThemeSettingsForm extends ConfigFormBase {

  /**
   * Scope element size.
   */
  public const SCOPE_SIZE = 30;

  /**
   * The key to store multiple groups in form state.
   */
  public const MULTIPLE_GROUPS_KEY = 'ui_skins_multiple_groups';

  /**
   * The CSS variables plugin manager.
   *
   * @var \Drupal\ui_skins\CssVariable\CssVariablePluginManagerInterface
   */
  protected CssVariablePluginManagerInterface $cssVariablePluginManager;

  /**
   * The transliteration service.
   *
   * @var \Drupal\Component\Transliteration\TransliterationInterface
   */
  protected TransliterationInterface $transliteration;

  /**
   * An array of configuration names that should be editable.
   *
   * @var array
   */
  protected array $editableConfig = [];

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    $instance = parent::create($container);
    $instance->cssVariablePluginManager = $container->get('plugin.manager.ui_skins.css_variable');
    $instance->transliteration = $container->get('transliteration');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return $this->editableConfig;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ui_skins.css_variables.theme_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $theme = ''): array {
    if (empty($theme)) {
      return $form;
    }

    $form_state->set('theme_name', $theme);
    $this->editableConfig = [
      $theme . '.settings',
    ];

    $grouped_plugin_definitions = $this->cssVariablePluginManager->getDefinitionsForTheme($theme);
    if (empty($grouped_plugin_definitions)) {
      return $form;
    }
    $form_state->set(static::MULTIPLE_GROUPS_KEY, TRUE);
    if (\count($grouped_plugin_definitions) == 1) {
      $form_state->set(static::MULTIPLE_GROUPS_KEY, FALSE);
    }

    /** @var array $ui_skins_css_variables_settings */
    $ui_skins_css_variables_settings = \theme_get_setting(UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY, $theme) ?? [];

    $form[UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY] = [
      '#type' => $form_state->get(static::MULTIPLE_GROUPS_KEY) ? 'vertical_tabs' : 'container',
      '#tree' => TRUE,
    ];

    foreach ($grouped_plugin_definitions as $group_plugin_definitions) {
      foreach ($group_plugin_definitions as $plugin_definition) {
        $plugin_element = $this->getPluginElements($plugin_definition, $ui_skins_css_variables_settings, $form_state);

        // Create group if it does not exist yet.
        if ($form_state->get(static::MULTIPLE_GROUPS_KEY) && $plugin_definition->hasCategory()) {
          $group_key = $this->getMachineName($plugin_definition->getCategory());
          if (!isset($form[UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY][$group_key])) {
            $form[UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY][$group_key] = [
              '#type' => 'details',
              '#title' => $plugin_definition->getCategory(),
              '#group' => UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY,
            ];
          }

          // @phpstan-ignore-next-line
          $form[UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY][$group_key][$plugin_definition->id()] = $plugin_element;
        }
        else {
          $form[UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY][$plugin_definition->id()] = $plugin_element;
        }
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    parent::validateForm($form, $form_state);
    $saved_variables = [];

    /** @var array $ui_skins_css_variables */
    $ui_skins_css_variables = $form_state->getValue(UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY, []);

    // Clean up vertical tabs form element value.
    if (isset($ui_skins_css_variables[UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY . '__active_tab'])) {
      unset($ui_skins_css_variables[UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY . '__active_tab']);
    }

    foreach ($ui_skins_css_variables as $root_plugin_id => $group_variables) {
      // Variable without group.
      if (isset($group_variables['values_container'])) {
        $this->filterPluginValues($saved_variables, $root_plugin_id, $group_variables['values_container']);
        continue;
      }

      foreach ($group_variables as $plugin_id => $plugin_scope_values) {
        $this->filterPluginValues($saved_variables, $plugin_id, $plugin_scope_values['values_container']);
      }
    }

    $form_state->setValue(UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY, $saved_variables);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $theme = $form_state->get('theme_name');
    $this->editableConfig = [
      $theme . '.settings',
    ];
    $values = $form_state->getValue(UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY);
    $config = $this->config($theme . '.settings');
    $config->set(UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY, $values)
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Callback for add new scope ajax buttons.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function addNewScopeCallback(array &$form, FormStateInterface $form_state): array {
    $triggering_element = $form_state->getTriggeringElement();
    // This case should not happen.
    if (!isset($triggering_element['#context']['plugin_id'])) {
      return [];
    }
    $plugin_id = $triggering_element['#context']['plugin_id'];
    /** @var \Drupal\ui_skins\Definition\CssVariableDefinition $plugin_definition */
    $plugin_definition = $this->cssVariablePluginManager->getDefinition($plugin_id, FALSE);
    if ($form_state->get(static::MULTIPLE_GROUPS_KEY) && $plugin_definition->hasCategory()) {
      $group_key = $this->getMachineName($plugin_definition->getCategory());
      return $form[UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY][$group_key][$plugin_id]['values_container'];
    }

    return $form[UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY][$plugin_id]['values_container'];
  }

  /**
   * Submit handler for the "add new scope" button.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function addNewScope(array &$form, FormStateInterface $form_state): void {
    $triggering_element = $form_state->getTriggeringElement();
    // This case should not happen.
    if (!isset($triggering_element['#context']['plugin_id'])) {
      return;
    }
    $plugin_id = $triggering_element['#context']['plugin_id'];
    $plugin_scopes_number_form_state_key = $plugin_id . '_scope_number';

    $plugin_scopes_number = $form_state->get($plugin_scopes_number_form_state_key);
    $form_state->set($plugin_scopes_number_form_state_key, $plugin_scopes_number + 1);

    $form_state->setRebuild();
  }

  /**
   * Get a plugin form elements.
   *
   * @param \Drupal\ui_skins\Definition\CssVariableDefinition $plugin_definition
   *   The CSS variable plugin definition.
   * @param array $ui_skins_css_variables_settings
   *   CSS variables theme settings.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The form elements.
   */
  protected function getPluginElements(CssVariableDefinition $plugin_definition, array $ui_skins_css_variables_settings, FormStateInterface $form_state): array {
    $element = [
      '#type' => 'details',
      '#title' => $plugin_definition->getLabel(),
      '#description' => $this->t('Variable: %variable_name<br>@description', [
        '@description' => $plugin_definition->getDescription(),
        '%variable_name' => UiSkinsUtility::getCssVariableName($plugin_definition->id()),
      ]),
      '#open' => TRUE,
      '#weight' => $plugin_definition->getWeight(),
      'values_container' => [
        '#type' => 'container',
        // Force an id because otherwise default id is changed when using AJAX.
        '#attributes' => [
          'id' => Html::getId('css-variable-values-wrapper-' . $plugin_definition->id()),
        ],
      ],
    ];

    $scopes_infos = $this->getPluginScopesInfos($plugin_definition, $ui_skins_css_variables_settings);
    $scope_number = 0;
    foreach ($scopes_infos as $scope => $infos) {
      $element['values_container'][$scope_number] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'form--inline',
            'clearfix',
          ],
        ],
        'scope' => [
          '#type' => 'textfield',
          '#title' => $this->t('Scope'),
          '#description' => $infos['disabled'] ? $this->t('This scope cannot be removed because it is defined in the <br /> variable declaration.') : '',
          '#default_value' => UiSkinsUtility::getCssScopeName($scope),
          '#disabled' => $infos['disabled'],
          '#size' => $this::SCOPE_SIZE,
        ],
        'value' => [
          '#type' => $plugin_definition->getType(),
          '#title' => $this->t('Value'),
          '#description' => $this->t('Default value: @default_value', [
            '@default_value' => $infos['default_value'],
          ]),
          '#default_value' => $infos['value'],
        ],
      ];
      ++$scope_number;
    }

    $plugin_scopes_number_form_state_key = $plugin_definition->id() . '_scope_number';
    $plugin_state_scopes_number = $form_state->get($plugin_scopes_number_form_state_key);
    if ($plugin_state_scopes_number === NULL) {
      $form_state->set($plugin_scopes_number_form_state_key, $scope_number);
      $plugin_state_scopes_number = $scope_number;
    }

    // Add additional scopes.
    for ($scope_number; $scope_number < $plugin_state_scopes_number; ++$scope_number) {
      $element['values_container'][$scope_number] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'form--inline',
            'clearfix',
          ],
        ],
        'scope' => [
          '#type' => 'textfield',
          '#title' => $this->t('Scope'),
          '#size' => $this::SCOPE_SIZE,
        ],
        'value' => [
          '#type' => $plugin_definition->getType(),
          '#title' => $this->t('Value'),
        ],
      ];
    }

    $element['actions'] = [
      '#type' => 'actions',
    ];
    $element['actions']['add_more_scope'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add new scope'),
      '#name' => Html::getId('add_more_scope_' . $plugin_definition->id()),
      '#submit' => [[$this, 'addNewScope']],
      '#ajax' => [
        'callback' => [$this, 'addNewScopeCallback'],
        'wrapper' => Html::getId('css-variable-values-wrapper-' . $plugin_definition->id()),
      ],
      '#context' => [
        'plugin_id' => $plugin_definition->id(),
      ],
    ];

    return $element;
  }

  /**
   * Extract values to save in configuration.
   *
   * @param array $saved_variables
   *   The filtered variables.
   * @param string $plugin_id
   *   The CSS variable plugin ID.
   * @param array $plugin_scope_values
   *   The scoped values.
   */
  protected function filterPluginValues(array &$saved_variables, string $plugin_id, array $plugin_scope_values): void {
    /** @var \Drupal\ui_skins\Definition\CssVariableDefinition $plugin_definition */
    $plugin_definition = $this->cssVariablePluginManager->getDefinition($plugin_id, FALSE);

    foreach ($plugin_scope_values as $plugin_scope_value) {
      $scope = $plugin_scope_value['scope'];
      $value = $plugin_scope_value['value'];

      // Remove entries where scope is empty.
      if (empty($scope)) {
        continue;
      }

      // Remove values that do not differ from the default values of the
      // plugin.
      if ($plugin_definition->isDefaultScopeValue($scope, $value)) {
        continue;
      }

      $saved_variables[$plugin_id][UiSkinsUtility::getConfigScopeName($scope)] = $value;
    }
  }

  /**
   * Get a plugin scope infos.
   *
   * @param \Drupal\ui_skins\Definition\CssVariableDefinition $plugin_definition
   *   The CSS variable plugin definition.
   * @param array $ui_skins_css_variables_settings
   *   CSS variables theme settings.
   *
   * @return array
   *   The form elements.
   */
  protected function getPluginScopesInfos(CssVariableDefinition $plugin_definition, array $ui_skins_css_variables_settings): array {
    $plugin_id = $plugin_definition->id();
    $scopes_infos = [];

    // Default scopes.
    foreach ($plugin_definition->getDefaultValues() as $default_scope => $default_value) {
      $default_scope = UiSkinsUtility::getConfigScopeName($default_scope);
      $scopes_infos[$default_scope] = [
        'value' => $default_value,
        'default_value' => $default_value,
        'disabled' => TRUE,
      ];

      if (isset($ui_skins_css_variables_settings[$plugin_id][$default_scope])) {
        $scopes_infos[$default_scope]['value'] = $ui_skins_css_variables_settings[$plugin_id][$default_scope];
        unset($ui_skins_css_variables_settings[$plugin_id][$default_scope]);
      }
    }

    // Remaining scopes in settings are scopes created using theme settings.
    if (isset($ui_skins_css_variables_settings[$plugin_id])) {
      foreach ($ui_skins_css_variables_settings[$plugin_id] as $new_scope => $value) {
        $new_scope = UiSkinsUtility::getConfigScopeName($new_scope);
        $scopes_infos[$new_scope] = [
          'value' => $value,
          'default_value' => $value,
          'disabled' => FALSE,
        ];
      }
    }

    return $scopes_infos;
  }

  /**
   * Generates a machine name from a string.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|string $string
   *   The string to convert.
   *
   * @return string
   *   The converted string.
   *
   * @see \Drupal\Core\Block\BlockBase::getMachineNameSuggestion()
   * @see \Drupal\system\MachineNameController::transliterate()
   */
  protected function getMachineName($string): string {
    $transliterated = $this->transliteration->transliterate($string, LanguageInterface::LANGCODE_DEFAULT, '_');
    $transliterated = \mb_strtolower($transliterated);
    $transliterated = \preg_replace('@[^a-z0-9_.]+@', '_', $transliterated);
    return $transliterated ?? '';
  }

}
