<?php

namespace Drupal\patterncss_ui\Form;

use Drupal\patterncss_ui\PatternCssManagerInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to duplicate pattern.
 *
 * @internal
 */
class PatternCssDuplicate extends FormBase {

  /**
   * The pattern id.
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
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Constructs a new patternDuplicate object.
   *
   * @param \Drupal\patterncss_ui\PatternCssManagerInterface $pattern_manager
   *   The Pattern selector manager.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(PatternCssManagerInterface $pattern_manager, TimeInterface $time) {
    $this->patternManager = $pattern_manager;
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('patterncss.pattern_manager'),
      $container->get('datetime.time'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'patterncss_duplicate_form';
  }

  /**
   * {@inheritdoc}
   *
   * @param array $form
   *   A nested array form elements comprising the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param int $pattern
   *   The pattern record ID to remove.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $pattern = 0) {
    $form['pattern_id'] = [
      '#type'  => 'value',
      '#value' => $pattern,
    ];

    // New selector to duplicate effect.
    $form['selector'] = [
      '#title'         => $this->t('Selector'),
      '#type'          => 'textfield',
      '#required'      => TRUE,
      '#size'          => 64,
      '#maxlength'     => 255,
      '#default_value' => '',
      '#description'   => $this->t('Here, you can use HTML tag, class with dot(.) and ID with hash(#) prefix. Be sure your selector has plain text content. e.g. ".page-title" or ".block-title".'),
      '#placeholder'   => $this->t('Enter valid selector'),
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type'        => 'submit',
      '#button_type' => 'primary',
      '#value'       => $this->t('Duplicate'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $pid      = $form_state->getValue('pattern_id');
    $is_new   = $pid == 0;
    $selector = trim($form_state->getValue('selector'));

    if ($is_new) {
      if ($this->patternManager->isPattern($selector)) {
        $form_state->setErrorByName('selector', $this->t('This selector is already exists.'));
      }
    }
    else {
      if ($this->patternManager->findById($pid)) {
        $pattern = $this->patternManager->findById($pid);

        if ($selector != $pattern['selector'] && $this->patternManager->isPattern($selector)) {
          $form_state->setErrorByName('selector', $this->t('This selector is already added.'));
        }
      }
    }
  }

  /**
   * Form submission handler for the 'duplicate' action.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   A reference to a keyed array containing the current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $pid      = $values['pattern_id'];
    $selector = trim($values['selector']);
    $label    = ucfirst(trim(preg_replace("/[^a-zA-Z0-9]+/", " ", $selector)));
    $status   = 1;

    $pattern = $this->patternManager->findById($pid);
    $segment = $pattern['segment'];
    $comment = $pattern['comment'];
    $options = $pattern['options'];

    // The Unix timestamp when the pattern was most recently saved.
    $changed = $this->time->getCurrentTime();

    // Save pattern.
    $new_pid = $this->patternManager->addPattern(0, $selector, $label, $segment, $comment, $changed, $status, $options);
    $this->messenger()
      ->addStatus($this->t('The selector %selector has been duplicated.', ['%selector' => $selector]));

    // Flush caches so the updated config can be checked.
    drupal_flush_all_caches();

    // Redirect to duplicated pattern edit form.
    $form_state->setRedirect('patterncss.edit', ['pattern' => $new_pid]);
  }

}
