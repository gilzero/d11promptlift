<?php

namespace Drupal\patterncss_ui;

use Drupal\patterncss_ui\Form\PatternCssFilter;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Displays Pattern CSS selector.
 *
 * @internal
 */
class PatternCssAdmin extends FormBase {

  /**
   * Pattern manager.
   *
   * @var \Drupal\patterncss_ui\PatternCssManagerInterface
   */
  protected $patternManager;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Constructs a new Pattern object.
   *
   * @param \Drupal\patterncss_ui\PatternCssManagerInterface $pattern_manager
   *   The Pattern selector manager.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   *   The current request.
   */
  public function __construct(PatternCssManagerInterface $pattern_manager, DateFormatterInterface $date_formatter, FormBuilderInterface $form_builder, Request $current_request) {
    $this->patternManager = $pattern_manager;
    $this->dateFormatter  = $date_formatter;
    $this->formBuilder    = $form_builder;
    $this->currentRequest = $current_request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('patterncss.pattern_manager'),
      $container->get('date.formatter'),
      $container->get('form_builder'),
      $container->get('request_stack')->getCurrentRequest(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pattern_admin_form';
  }

  /**
   * {@inheritdoc}
   *
   * @param array $form
   *   A nested array form elements comprising the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param string $pattern
   *   (optional) CSS Selector to be added to Pattern.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $pattern = '') {
    // Attach PatternCSS overview admin library.
    $form['#attached']['library'][] = 'patterncss_ui/pattern-list';

    $search  = $this->currentRequest->query->get('search');
    $segment = $this->currentRequest->query->get('segment') ?? NULL;
    $status  = $this->currentRequest->query->get('status') ?? NULL;

    /** @var \Drupal\patterncss_ui\Form\PatternCssFilter $form */
    $form['patterncss_admin_filter_form'] = $this->formBuilder->getForm(PatternCssFilter::class, $search, $segment, $status);
    $form['#attributes']['class'][] = 'patterncss-filter';
    $form['#attributes']['class'][] = 'views-exposed-form';

    $header = [
      [
        'data'  => $this->t('Selector'),
        'field' => 'p.pid',
      ],
      [
        'data'  => $this->t('Label'),
        'field' => 'p.label',
      ],
      [
        'data'  => $this->t('Segment'),
        'field' => 'p.segment',
      ],
      [
        'data'  => $this->t('Status'),
        'field' => 'p.status',
      ],
      [
        'data'  => $this->t('Updated'),
        'field' => 'p.changed',
        'sort'  => 'desc',
      ],
      $this->t('Operations'),
    ];

    $rows = [];
    $result = $this->patternManager->findAll($header, $search, $segment, $status);
    foreach ($result as $pattern) {
      $row = [];
      $row['selector'] = $pattern->selector;
      $row['label'] = $pattern->label;
      $row['segment'] = $pattern->segment;
      $status_class = $pattern->status ? 'marker marker--enabled' : 'marker';
      $row['status'] = [
        'data' => [
          '#type' => 'markup',
          '#prefix' => '<span class="' . $status_class . '">',
          '#suffix' => '</span>',
          '#markup' => $pattern->status ? $this->t('Enabled') : $this->t('Disabled'),
        ],
      ];
      $row['changed'] = $this->dateFormatter->format($pattern->changed, 'short');
      $links = [];
      $links['edit'] = [
        'title' => $this->t('Edit'),
        'url'   => Url::fromRoute('patterncss.edit', ['pattern' => $pattern->pid]),
      ];
      $links['delete'] = [
        'title' => $this->t('Delete'),
        'url'   => Url::fromRoute('patterncss.delete', ['pattern' => $pattern->pid]),
      ];
      $links['duplicate'] = [
        'title' => $this->t('Duplicate'),
        'url'   => Url::fromRoute('patterncss.duplicate', ['pattern' => $pattern->pid]),
      ];
      $row[] = [
        'data' => [
          '#type'  => 'operations',
          '#links' => $links,
        ],
      ];
      $rows[] = $row;
    }

    $form['patterncss_admin_table'] = [
      '#type'   => 'table',
      '#header' => $header,
      '#rows'   => $rows,
      '#empty'  => $this->t('No pattern CSS selector available. <a href=":link">Add pattern</a> .', [
        ':link' => Url::fromRoute('patterncss.add')
          ->toString(),
      ]),
      '#attributes' => ['class' => ['patterncss-list']],
    ];

    $form['pager'] = ['#type' => 'pager'];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // @todo Add operations to pattern CSS selector list
  }

}
