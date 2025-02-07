<?php

namespace Drupal\metatag_ai\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Stores metadata content type settings.
 */
class MetatagAISettingsForm extends ConfigFormBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'metatag_ai.content_settings',
    ];
  }

  /**
   * Constructs a MetatagAIConfigForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'metadata_content_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('metatag_ai.content_settings');
    $selected_content_types = $config->get('metatag_ai.metadata_content_types');

    $form = [
      '#attributes' => ['enctype' => 'multipart/form-data'],
    ];

    $form['notification_instructions'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Instructions'),
      '#markup' => $this->t('Select the content type(s) for which you want the Metatag AI features to be used.'),
    ];

    $content_types = $this->entityTypeManager->getStorage('node_type')->loadMultiple();

    $options = [];
    foreach ($content_types as $content_type) {
      $options[$content_type->id()] = $content_type->label();
    }

    $form['metadata_content_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Content Types'),
      '#options' => $options,
      '#default_value' => $selected_content_types ? $selected_content_types : [],
    ];

    $form['metadata_field_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Field machine name'),
      '#default_value' => $config->get('metatag_ai.metadata_field_id') ?: 'field_metatag',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $checked_values = $form_state->getValue('metadata_content_types');
    $this->config('metatag_ai.content_settings')
      ->set('metatag_ai.metadata_content_types', $checked_values)
      ->set('metatag_ai.metadata_field_id', $form_state->getValue('metadata_field_id'))
      ->save();
  }

}
