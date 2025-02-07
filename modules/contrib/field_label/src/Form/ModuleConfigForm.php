<?php

namespace Drupal\field_label\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ModuleConfigForm.
 *
 * Configuration settings for Field Label module.
 */
class ModuleConfigForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'field_label.settings';

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'field_label_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    // Field state helper.
    $depends_on = function ($name, $states) {
      $conditions = [];
      foreach ($states as $state) {
        $conditions[$state] = [
          ':input[name="' . $name . '"]' => [
            'checked' => TRUE,
          ],
        ];
      }
      return $conditions;
    };

    // Label value settings.
    $form['label_value'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Label Values'),
    ];

    $form['label_value']['label_value_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable label value'),
      '#description' => $this->t('Allow authorized users to change the field label text.'),
      '#default_value' => $config->get('label_value_enabled'),
    ];

    $form['label_value']['plural_label_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable plural label'),
      '#description' => $this->t('Allow authorized users to add a plural variation to the field label text when field cardinality is multiple.'),
      '#default_value' => $config->get('plural_label_enabled'),
      '#states' => $depends_on('label_value_enabled', ['visible']),
    ];

    // Label class settings.
    $form['label_class'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Label Classes'),
    ];

    $form['label_class']['label_class_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable label classes field'),
      '#description' => $this->t('Allow authorized users to add "free-form" list of CSS classes to the label tag.'),
      '#default_value' => $config->get('label_class_enabled'),
    ];

    $form['label_class']['label_class_select_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable label class select list'),
      '#description' => $this->t('Allow authorized users to select a CSS class from a list of configured classes.'),
      '#default_value' => $config->get('label_class_select_enabled'),
    ];

    // Classs list container.
    $form['label_class']['container'] = [
      '#type' => 'container',
      '#states' => $depends_on('label_class_select_enabled', ['visible']),
    ];

    $form['label_class']['container']['class_list'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Class list'),
      '#description' => $this->t('Enter one or more classes on each line in the format: <code>.classA.classB|Label</code>. <b>Example:</b><br><code>.title|Title<br>.title.title--large|Large title</code>'),
      '#default_value' => $config->get('class_list'),
      '#states' => $depends_on('label_class_select_enabled', ['required']),
    ];

    // Label tag settings.
    $form['label_tag'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Label Tags'),
    ];

    $form['label_tag']['label_tag_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable label tag selection'),
      '#description' => $this->t('Allow authorized users to select a wrapper from a list of allowed HTML tags.'),
      '#default_value' => $config->get('label_tag_enabled'),
    ];

    $form['label_tag']['container'] = [
      '#type' => 'container',
      '#states' => $depends_on('label_tag_enabled', ['visible']),
    ];

    $form['label_tag']['container']['allowed_tags'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Allowed label tags'),
      '#description' => $this->t('A space-separated list of HTML tags that should appear in the "Label tag" setting dropdown.'),
      '#default_value' => implode(' ', array_filter($config->get('allowed_tags'))),
      '#states' => $depends_on('label_tag_enabled', ['required']),
    ];

    $form['label_tag']['container']['instructions'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t("<strong>IMPORTANT:</strong><br>If your theme provides/overrides field templates, you may need to modify them to use Field Label's <code><b>label_tag</b></code> twig variable. For example:<br><code>&lt;{{ <b>label_tag</b>|default('div') }}{{ title_attributes }}&gt;{{ label }}&lt;/{{ <b>label_tag</b>|default('div') }}&gt;</code>"),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $config = $this->configFactory()->getEditable(static::SETTINGS);

    // Set all feature 'enabled' values.
    $config->set('label_value_enabled', $form_state->getValue('label_value_enabled'))
      ->set('label_class_enabled', $form_state->getValue('label_class_enabled'))
      ->set('label_class_select_enabled', $form_state->getValue('label_class_select_enabled'))
      ->set('class_list', $form_state->getValue('class_list'))
      ->set('label_tag_enabled', $form_state->getValue('label_tag_enabled'))
      ->set('plural_label_enabled', $form_state->getValue('label_value_enabled') ? $form_state->getValue('plural_label_enabled') : FALSE);

    // Process allowed tags even if disabled to keep any previous values.
    $tags = array_filter(explode(' ', $form_state->getValue('allowed_tags')));
    $config->set('allowed_tags', $tags);

    $config->save();

    parent::submitForm($form, $form_state);
  }

}
