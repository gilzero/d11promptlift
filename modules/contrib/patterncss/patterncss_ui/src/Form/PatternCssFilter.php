<?php

namespace Drupal\patterncss_ui\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the path admin overview filter form.
 *
 * @internal
 */
class PatternCssFilter extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'patterncss_admin_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $keys = NULL, $segment = NULL, $status = NULL) {
    $form['basic'] = [
      '#type'       => 'container',
      '#attributes' => ['class' => ['container-inline']],
    ];
    $form['basic']['filter'] = [
      '#type'          => 'search',
      '#title'         => $this->t('Keyboard'),
      '#default_value' => $keys,
      '#maxlength'     => 128,
      '#size'          => 25,
    ];
    $segments = ['' => $this->t('- Any -')] + patterncss_segments();
    $form['basic']['segment'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Segment'),
      '#options'       => $segments,
      '#default_value' => $segment,
    ];
    $form['basic']['status'] = [
      '#type'    => 'select',
      '#title'   => $this->t('Status'),
      '#options' => [
        '' => $this->t('- Any -'),
        1  => $this->t('Enabled'),
        0  => $this->t('Disabled'),
      ],
      '#default_value' => $status,
    ];
    $form['basic']['submit'] = [
      '#type'  => 'submit',
      '#value' => $this->t('Filter'),
    ];
    if ($keys || (!is_null($segment) && $segment != '') || (!is_null($status) && $status != '')) {
      $form['basic']['reset'] = [
        '#type'   => 'submit',
        '#value'  => $this->t('Reset'),
        '#submit' => ['::resetForm'],
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('patterncss.admin', [], [
      'query' => [
        'search'  => trim($form_state->getValue('filter')),
        'segment' => $form_state->getValue('segment'),
        'status'  => $form_state->getValue('status'),
      ],
    ]);
  }

  /**
   * Resets the filter selections.
   */
  public function resetForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('patterncss.admin');
  }

}
