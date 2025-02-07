<?php

namespace Drupal\menu_item_fields\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\system\Plugin\Block\SystemMenuBlock;
use Drupal\Core\Url;

/**
 * Provides a drupal menu that uses display view modes.
 *
 * @Block(
 *   id = "menu_item_fields",
 *   admin_label = @Translation("Menu with fields"),
 *   deriver = "Drupal\system\Plugin\Derivative\SystemMenuBlock",
 *   category = @Translation("Menu Item Fields")
 * )
 */
class FieldMenuBlock extends SystemMenuBlock {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $defaultConfiguration = parent::defaultConfiguration() + [
      'view_mode' => 'default',
      'view_mode_override_field' => '_none',
    ];
    return $defaultConfiguration;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $viewModes = \Drupal::entityTypeManager()->getStorage('entity_view_mode')->loadByProperties([
      'targetEntityType' => 'menu_link_content',
    ]);
    $viewModeOptions = ['default' => $this->t('Default')];
    foreach ($viewModes as $viewMode) {
      $id = substr($viewMode->id(), strlen('menu_link_content.'));
      $viewModeOptions[$id] = $viewMode->label();
    }

    $form['view_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('View mode to use when rendering the menu items'),
      '#default_value' => $this->configuration['view_mode'],
      '#options' => $viewModeOptions,
    ];
    // @todo inject this dependency.
    $moduleHandler = \Drupal::service('module_handler');
    if ($moduleHandler->moduleExists('menu_item_fiels_ui')) {
      $form['view_mode']['#description'] = $this->t('View mode to use when rendering menu items. <a href="@url">Configure view modes</a>', [
        '@url' => Url::fromRoute('entity.entity_view_display.menu_link_content.default')->toString(),
      ]);
    }

    $fields = \Drupal::entityTypeManager()->getStorage('field_config')->loadByProperties([
      'entity_type' => 'menu_link_content',
    ]);
    $fieldOptions = ['_none' => $this->t('None')];
    foreach ($fields as $field) {
      [,, $id] = explode('.', $field->id());
      $fieldOptions[$id] = $field->label();
    }

    $form['view_mode_override_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Field that stores the view mode override per menu item'),
      '#default_value' => $this->configuration['view_mode_override_field'],
      '#description' => $this->t('This field will usually be a an options field with the available view mode ids.'),
      '#options' => $fieldOptions,
    ];
    $form += parent::blockForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['view_mode'] = $form_state->getValue('view_mode');
    $this->configuration['view_mode_override_field'] = $form_state->getValue('view_mode_override_field');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = parent::build();
    if (isset($build['#theme'])) {
      $build['#theme'] = $this->addThemeSuggestion($build['#theme']);
    }
    $build['#view_mode'] = $this->configuration['view_mode'];
    if ($this->configuration['view_mode_override_field'] != '_none') {
      $build['#view_mode_override_field'] = $this->configuration['view_mode_override_field'];
    }

    // @todo inject this dependency.
    $moduleHandler = \Drupal::service('module_handler');
    if ($moduleHandler->moduleExists('menu_ui')) {
      $menuName = $this->getDerivativeId();
      $build['#contextual_links']['menu'] = [
        'route_parameters' => ['menu' => $menuName],
      ];
    }

    return $build;
  }

  /**
   * Add a suggestion to be able to overwrite menu links markup.
   */
  protected function addThemeSuggestion($themeHook) {
    return preg_replace('/^menu__/', 'menu__field_content__', $themeHook);
  }

}
