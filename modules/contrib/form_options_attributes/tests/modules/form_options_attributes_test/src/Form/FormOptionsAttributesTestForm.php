<?php

namespace Drupal\form_options_attributes_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
class FormOptionsAttributesTestForm extends FormBase {
  
  public function getFormId() {
    return 'form_options_attributes_module_test_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $states = [
      'AL' => $this->t('Alabama'),
      'AK' => $this->t('Alaska'),
      'AZ' => $this->t('Arizona'),
      'AR' => $this->t('Arkansas'),
      // ..
      'WI' => $this->t('Wisconsin'),
      'WY' => $this->t('Wyoming'),
    ];
    $states_attributes = [
      'AL' => ['class' => ['southeast'], 'data-bbq-meat' => 'pork'],
      'AK' => ['class' => ['non-contiguous'], 'data-bbq-meat' => 'salmon'],
      'AZ' => ['class' => ['southwest'], 'data-bbq-meat' => 'rattlesnake'],
      'AR' => ['class' => ['south'], 'data-bbq-meat' => 'beef'],
      // ...
      'WI' => ['class' => ['midwest'], 'data-bbq-meat' => 'cheese'],
      'WY' => ['class' => ['flyover'], 'data-bbq-meat' => 'bison'],
    ];
    $states_wrapper_attributes = [
      'AL' => ['class' => ['southeast-wrapper'], 'data-bbq-meat' => 'pork'],
      'AK' => ['class' => ['non-contiguous-wrapper'], 'data-bbq-meat' => 'salmon'],
      'AZ' => ['class' => ['southwest-wrapper'], 'data-bbq-meat' => 'rattlesnake'],
      'AR' => ['class' => ['south-wrapper'], 'data-bbq-meat' => 'beef'],
      // ...
      'WI' => ['class' => ['midwest-wrapper'], 'data-bbq-meat' => 'cheese'],
      'WY' => ['class' => ['flyover-wrapper'], 'data-bbq-meat' => 'bison'],
    ];
    $states_label_attributes = [
      'AL' => ['class' => ['southeast-label'], 'data-bbq-meat' => 'pork'],
      'AK' => ['class' => ['non-contiguous-label'], 'data-bbq-meat' => 'salmon'],
      'AZ' => ['class' => ['southwest-label'], 'data-bbq-meat' => 'rattlesnake'],
      'AR' => ['class' => ['south-label'], 'data-bbq-meat' => 'beef'],
      // ...
      'WI' => ['class' => ['midwest-label'], 'data-bbq-meat' => 'cheese'],
      'WY' => ['class' => ['flyover-label'], 'data-bbq-meat' => 'bison'],
    ];
    $form['states'] = [
      '#type' => 'select',
      '#title' => $this->t('States'),
      '#options' => $states,
      '#options_attributes' => $states_attributes,
      '#attributes' => ['class' => ['states-bbq-selector']],
    ];

    $form['states_radio'] = [
      '#type' => 'radios',
      '#title' => $this->t('States'),
      '#options' => $states,
      '#options_attributes' => $states_attributes,
      '#options_wrapper_attributes' => $states_wrapper_attributes,
      '#options_label_attributes' => $states_label_attributes,
      '#attributes' => ['class' => ['states-bbq-selector-radios']],
    ];

    $form['states_checkboxes'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('States'),
      '#options' => $states,
      '#options_attributes' => $states_attributes,
      '#options_wrapper_attributes' => $states_wrapper_attributes,
      '#options_label_attributes' => $states_label_attributes,
      '#attributes' => ['class' => ['states-bbq-selector-checkboxes']],
    ];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    
  }


}
