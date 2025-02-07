<?php

namespace Drupal\patterncss_ui\Form;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\patterncss_ui\PatternCssManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Patterncss add and edit pattern form.
 *
 * @internal
 */
class PatternCssForm extends FormBase {

  /**
   * An array to store the variables.
   *
   * @var array
   */
  protected $variables = [];

  /**
   * Pattern manager.
   *
   * @var \Drupal\patterncss_ui\PatternCssManagerInterface
   */
  protected $patternManager;

  /**
   * A config object for the pattern settings.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The file URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Constructs a new Pattern object.
   *
   * @param \Drupal\patterncss_ui\PatternCssManagerInterface $pattern_manager
   *   The Pattern selector manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A config factory for retrieving required config objects.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator
   *   The file URL generator.
   */
  public function __construct(PatternCssManagerInterface $pattern_manager, ConfigFactoryInterface $config_factory, TimeInterface $time, ModuleHandlerInterface $module_handler, FileUrlGeneratorInterface $file_url_generator) {
    $this->patternManager = $pattern_manager;
    $this->config = $config_factory->get('patterncss.settings');
    $this->time = $time;
    $this->moduleHandler = $module_handler;
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('patterncss.pattern_manager'),
      $container->get('config.factory'),
      $container->get('datetime.time'),
      $container->get('module_handler'),
      $container->get('file_url_generator'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'patterncss_form';
  }

  /**
   * {@inheritdoc}
   *
   * @param array $form
   *   A nested array form elements comprising the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param int $pattern
   *   (optional) Pattern id to be passed on to
   *   \Drupal::formBuilder()->getForm() for use as the default value of the
   *   Pattern ID form data.
   */
  public function buildForm(array $form, FormStateInterface $form_state, int $pattern = 0) {
    // Attach PatternCSS form library.
    $form['#attached']['library'][] = 'patterncss_ui/pattern-form';

    // Prepare PatternCSS form default values.
    $pid      = $pattern;
    $pattern  = $this->patternManager->findById($pattern) ?? [];
    $selector = $pattern['selector'] ?? '';
    $label    = $pattern['label'] ?? '';
    $comment  = $pattern['comment'] ?? '';
    $status   = $pattern['status'] ?? TRUE;
    $options  = [];

    // Handle the case when $pattern is not an array or option is not set.
    if (is_array($pattern) && isset($pattern['options'])) {
      $options = unserialize($pattern['options'], ['allowed_classes' => FALSE]) ?? '';
    }

    // Store pattern id.
    $form['pattern_id'] = [
      '#type'  => 'value',
      '#value' => $pid,
    ];

    // Store variables.
    $form['variables'] = [
      '#type'  => 'value',
      '#value' => $this->variables,
    ];

    // Load the PatternCSS configuration settings.
    $config = $this->config;

    // The default selector to use when detecting multiple texts to pattern.
    $form['selector'] = [
      '#title'         => $this->t('Selector'),
      '#type'          => 'textfield',
      '#required'      => TRUE,
      '#size'          => 64,
      '#maxlength'     => 256,
      '#default_value' => $selector,
      '#description'   => $this->t('Enter a valid element or a css selector.'),
    ];

    // The label of this selector.
    $form['label'] = [
      '#title'         => $this->t('Label'),
      '#type'          => 'textfield',
      '#required'      => FALSE,
      '#size'          => 64,
      '#maxlength'     => 64,
      '#default_value' => $label ?? '',
      '#description'   => $this->t('The label for this pattern selector like <em>About block title</em>.'),
    ];

    // PatternCSS utilities.
    $form['options'] = [
      '#title' => $this->t('Pattern options'),
      '#type'  => 'details',
      '#open'  => TRUE,
    ];

    // The pattern segment.
    $form['options']['segment'] = [
      '#title'         => $this->t('Segment'),
      '#type'          => 'select',
      '#options'       => patterncss_segments(),
      '#default_value' => $pattern['segment'] ?? $config->get('options.segment'),
      '#attributes'    => ['class' => ['segment']],
    ];

    // The pattern to use.
    $form['options']['pattern'] = [
      '#title'         => $this->t('Pattern'),
      '#type'          => 'select',
      '#options'       => patterncss_patterns(),
      '#default_value' => $options['pattern'] ?? $config->get('options.pattern'),
      '#description'   => $this->t('Select the pattern name you want to use for CSS selectors.'),
      '#attributes'    => ['class' => ['pattern']],
    ];

    $form['region'] = [
      '#type'       => 'container',
      '#attributes' => [
        'id'    => 'patterncss_sidebar',
        'class' => ['patterncss-secondary-region'],
      ],
    ];

    // Pattern.css preview.
    $form['region']['preview'] = [
      '#type'  => 'details',
      '#title' => $this->t('Pattern preview'),
      '#open'  => TRUE,
    ];

    // Pattern.css preview and reload.
    $module_path = $this->moduleHandler->getModule('patterncss_ui')->getPath();
    $image_path = $this->fileUrlGenerator->generateAbsoluteString($module_path . '/img/patterncss-image-sample.png');
    $form['region']['preview']['sample'] = [
      '#type'   => 'markup',
      '#markup' => '<div class="pattern__preview"><div class="sample__background"></div><h3 class="sample__text">EASY</h3><div class="sample__shadow"><h4 class="white">PATTERN CSS MODULE</h4></div><div class="sample__separator"></div><div class="sample__image"><img src="' . $image_path . '"></div></div>',
    ];
    $form['region']['preview']['replay'] = [
      '#value'      => $this->t('Rebuild'),
      '#type'       => 'image_button',
      '#name'       => 'image_button',
      '#src'        => 'data:image/png;base64,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',
      '#attributes' => ['class' => ['pattern__replay']],
    ];

    // The pattern style options.
    $form['region']['pattern_style'] = [
      '#title' => $this->t('Style'),
      '#type'  => 'details',
      '#open'  => TRUE,
    ];

    // The pattern color.
    $form['region']['pattern_style']['color'] = [
      '#title'         => $this->t('Pattern color'),
      '#type'          => 'textfield',
      '#default_value' => $options['color'] ?? '#003ecc',
      '#attributes'    => ['class' => ['pattern', 'coloris', 'instance']],
      '#field_prefix'  => '<div class="circle">',
      '#field_suffix'  => '</div>',
    ];

    // The pattern background color.
    $form['region']['pattern_style']['background'] = [
      '#title'         => $this->t('Background color'),
      '#type'          => 'textfield',
      '#default_value' => $options['background'] ?? 'transparent',
      '#attributes'    => ['class' => ['pattern', 'coloris', 'instance']],
      '#field_prefix'  => '<div class="square">',
      '#field_suffix'  => '</div>',
    ];

    // The pattern background position.
    $form['region']['pattern_style']['position'] = [
      '#type'          => 'hidden',
      '#default_value' => $options['position'] ?? 'center center',
      '#attributes'    => ['id' => 'pattern-position'],
    ];
    $form['region']['pattern_style']['background_position'] = [
      '#title'        => $this->t('Background position'),
      '#type'         => 'markup',
      '#markup'       => '<div id="background-position"><div class="position-top"><a class="left-top" title="left top"></a><a class="center-top" title="center top"></a><a class="right-top" title="right top"></a></div><div class="position-center"><a class="left-center" title="left center"></a><a class="center-center active" title="center center"></a><a class="right-center" title="right center"></a></div><div class="position-bottom"><a class="left-bottom" title="left bottom"></a><a class="center-bottom" title="center bottom"></a><a class="right-bottom" title="right bottom"></a></div></div>',
      '#allowed_tags' => ['div', 'a'],
    ];

    // The pattern size.
    $sizes = patterncss_sizes();
    $datalist_options = '';
    foreach ($sizes as $value => $label) {
      $datalist_options .= '<option value="' . $value . '" label="' . $label . '"></option>';
    }
    $form['region']['pattern_style']['size'] = [
      '#title'         => $this->t('Size'),
      '#type'          => 'range',
      '#list'          => 'sizes',
      '#min'           => 1,
      '#max'           => 5,
      '#step'          => 1,
      '#default_value' => $options['size'] ?? $config->get('options.size'),
      '#attributes'    => ['class' => ['pattern', 'size']],
    ];
    $form['region']['pattern_style']['sizes'] = [
      '#title'        => $this->t('Pattern sizes'),
      '#type'         => 'markup',
      '#markup'       => '<div class="size-range"><datalist id="sizes">' . $datalist_options . '</datalist></div>',
      '#allowed_tags' => ['datalist', 'option'],
    ];

    // The pattern image transform options.
    $form['region']['transform'] = [
      '#title'  => $this->t('Transform'),
      '#type'   => 'details',
      '#open'   => TRUE,
      '#states' => [
        'visible' => [
          'select[name="segment"]' => ['value' => 'image'],
        ],
      ],
    ];
    $translate_x = $options['translateX'] ?? 0;
    $form['region']['transform']['translate_x'] = [
      '#title'         => $this->t('Translate X'),
      '#type'          => 'range',
      '#list'          => 'sizes',
      '#min'           => -200,
      '#max'           => 200,
      '#step'          => 1,
      '#default_value' => $translate_x,
      '#attributes'    => ['class' => ['pattern', 'size']],
      '#field_suffix'  => '<div id="translate-x-value">' . $translate_x . '</div>',
    ];
    $translate_y = $options['translateY'] ?? 0;
    $form['region']['transform']['translate_y'] = [
      '#title'         => $this->t('Translate Y'),
      '#type'          => 'range',
      '#list'          => 'sizes',
      '#min'           => -200,
      '#max'           => 200,
      '#step'          => 1,
      '#default_value' => $translate_y,
      '#attributes'    => ['class' => ['pattern', 'size']],
      '#field_suffix'  => '<div id="translate-y-value">' . $translate_y . '</div>',
    ];

    // The separator height options.
    $form['region']['separator'] = [
      '#title'  => $this->t('Height'),
      '#type'   => 'details',
      '#open'   => TRUE,
      '#states' => [
        'visible' => [
          'select[name="segment"]' => ['value' => 'separator'],
        ],
      ],
    ];
    $form['region']['separator']['height'] = [
      '#title'         => $this->t('Height'),
      '#type'          => 'range',
      '#min'           => 1,
      '#max'           => 180,
      '#step'          => 1,
      '#default_value' => $options['height'] ?? 6,
      '#attributes'    => ['class' => ['pattern', 'height']],
      '#field_prefix'  => '<div id="height-value"></div>',
    ];

    // Sidebar close button.
    $close_sidebar_translation = $this->t('Close sidebar panel');
    $form['region']['sidebar_close'] = [
      '#markup' => '<a href="#close-sidebar" class="toggle-sidebar__close trigger" role="button" title="' . $close_sidebar_translation . '"><span class="visually-hidden">' . $close_sidebar_translation . '</span></a>',
    ];

    // The comment for describe pattern settings and usage in website.
    $form['comment'] = [
      '#type'          => 'textarea',
      '#title'         => $this->t('Comment'),
      '#default_value' => $comment ?? '',
      '#description'   => $this->t('Describe this pattern settings and usage in your website.'),
      '#rows'          => 2,
      '#weight'        => 96,
    ];

    $form['sticky'] = [
      '#type'       => 'container',
      '#attributes' => ['class' => ['gin-sticky', 'gin-sticky-form-actions']],
    ];

    // Enabled status for this pattern.
    $form['sticky']['status'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Enabled'),
      '#default_value' => $status ?? FALSE,
      '#weight'        => 99,
    ];

    // Add sidebar toggle.
    $hide_panel = $this->t('Hide sidebar panel');
    $form['sticky']['sidebar_toggle'] = [
      '#markup' => '<a href="#toggle-sidebar" class="toggle-sidebar__trigger trigger" role="button" title="' . $hide_panel . '" aria-controls="patterncss_sidebar"><span class="visually-hidden">' . $hide_panel . '</span></a>',
      '#weight' => '999',
    ];
    $form['sidebar_overlay'] = [
      '#markup' => '<div class="toggle-sidebar__overlay trigger"></div>',
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type'        => 'submit',
      '#value'       => $this->t('Save'),
      '#button_type' => 'primary',
      '#submit'      => [[$this, 'submitForm']],
    ];

    if ($pid != 0) {
      // Add a 'Remove' button for pattern form.
      $form['actions']['delete'] = [
        '#type'       => 'link',
        '#title'      => $this->t('Delete'),
        '#url'        => Url::fromRoute('patterncss.delete', ['pattern' => $pid]),
        '#attributes' => [
          'class' => [
            'action-link',
            'action-link--danger',
            'action-link--icon-trash',
          ],
        ],
      ];

      // Redirect to list for submit handler on edit form.
      $form['actions']['submit']['#submit'] = ['::submitForm', '::overview'];
    }
    else {
      // Add a 'Save and go to list' button for add form.
      $form['actions']['overview'] = [
        '#type'   => 'submit',
        '#value'  => $this->t('Save and go to list'),
        '#submit' => array_merge($form['actions']['submit']['#submit'], ['::overview']),
        '#weight' => 20,
      ];
    }

    return $form;
  }

  /**
   * Submit handler for removing pattern.
   *
   * @param array[] $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function remove(array &$form, FormStateInterface $form_state) {
    $pid = $form_state->getValue('pattern_id');
    $form_state->setRedirect('patterncss.delete', ['pattern' => $pid]);
  }

  /**
   * Form submission handler for the 'overview' action.
   *
   * @param array[] $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function overview(array $form, FormStateInterface $form_state): void {
    $form_state->setRedirect('patterncss.admin');
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
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Store main pattern data column.
    $pid      = $values['pattern_id'];
    $label    = trim($values['label']);
    $selector = trim($values['selector']);
    $comment  = trim($values['comment']);
    $segment  = $values['segment'];
    $status   = $values['status'];

    // Provide a label from selector if was empty.
    if (empty($label)) {
      $label = ucfirst(trim(preg_replace("/[^a-zA-Z0-9]+/", " ", $selector)));
    }

    // Set variables from main pattern settings.
    $variables['pattern']    = $values['pattern'];
    $variables['color']      = $values['color'];
    $variables['background'] = $values['background'];
    $variables['position']   = $values['position'];
    $variables['size']       = $values['size'];
    $variables['height']     = $values['height'] ?? '5px';
    $variables['translateX'] = $values['translate_x'] ?? '30px';
    $variables['translateY'] = $values['translate_y'] ?? '30px';

    // Serialize options variables.
    $options = serialize($variables);

    // The Unix timestamp when the pattern was most recently saved.
    $changed = $this->time->getCurrentTime();

    // Save pattern.
    $this->patternManager->addPattern($pid, $selector, $label, $segment, $comment, $changed, $status, $options);
    $this->messenger()
      ->addStatus($this->t('The selector %selector has been added.', ['%selector' => $selector]));

    // Flush caches so the updated config can be checked.
    drupal_flush_all_caches();
  }

}
