<?php

namespace Drupal\patterncss_ui\Form;

use Drupal\patterncss_ui\PatternCssManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a form to remove CSS selector.
 *
 * @internal
 */
class PatternCssDelete extends ConfirmFormBase {

  /**
   * The Pattern selector.
   *
   * @var int
   */
  protected $pattern;

  /**
   * The Pattern selector manager.
   *
   * @var \Drupal\patterncss_ui\PatternCssManagerInterface
   */
  protected $patternManager;

  /**
   * Constructs a new patternDelete object.
   *
   * @param \Drupal\patterncss_ui\PatternCssManagerInterface $pattern_manager
   *   The Pattern selector manager.
   */
  public function __construct(PatternCssManagerInterface $pattern_manager) {
    $this->patternManager = $pattern_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('patterncss.pattern_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'patterncss_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to remove %selector from pattern selectors?', ['%selector' => $this->pattern['selector']]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   *
   * @param array $form
   *   A nested array form elements comprising the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param string $pattern
   *   The Pattern record ID to remove.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $pattern = '') {
    if (!$this->pattern = $this->patternManager->findById($pattern)) {
      throw new NotFoundHttpException();
    }
    $form['pattern_id'] = [
      '#type'  => 'value',
      '#value' => $pattern,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $pattern_id = $form_state->getValue('pattern_id');
    $this->patternManager->removePattern($pattern_id);
    $this->logger('user')
      ->notice('Deleted %selector', ['%selector' => $this->pattern['selector']]);
    $this->messenger()
      ->addStatus($this->t('The Pattern selector %selector was deleted.', ['%selector' => $this->pattern['selector']]));

    // Flush caches so the updated config can be checked.
    drupal_flush_all_caches();

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('patterncss.admin');
  }

}
