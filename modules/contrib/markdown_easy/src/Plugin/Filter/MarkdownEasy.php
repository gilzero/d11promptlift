<?php

declare(strict_types=1);

namespace Drupal\markdown_easy\Plugin\Filter;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A simple Markdown text filter.
 *
 * @Filter(
 *   id = "markdown_easy",
 *   title = @Translation("Markdown Easy"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
 *   description = @Translation("A simple Markdown filter. You will need to configure the 'Limit allowed HTML tags and correct faulty HTML' filter so that it runs after this filter. See the README to disable this requirement."),
 *   settings = {
 *     "flavor" = "standard"
 *   },
 * )
 */
class MarkdownEasy extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * Constructs a FormatterBase object.
   *
   * @param array<mixed> $configuration
   *   The configuration of the filter.
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  final public function __construct(array $configuration, $plugin_id, $plugin_definition, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
    $form['flavor'] = [
      '#type' => 'select',
      '#title' => $this->t('Flavor'),
      '#default_value' => $this->settings['flavor'],
      '#required' => TRUE,
      '#options' => [
        'standard' => $this->t('Standard Markdown'),
        'github' => $this->t('GitHub-flavored Markdown'),
      ],
    ];

    $form['tips'] = [
      '#type' => 'item',
      '#title' => $this->t('Important'),
      '#description' => $this->t('<ul><li>The Markdown Easy filter should run before the "Convert line breaks into HTML(i . e . < br > and < p > )" filter.)</li><li>The Markdown Easy filter should run before the "Limit allowed HTML tags and correct faulty HTML" filter. It is strongly recommended to use these filters together.</li></ul>'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode): FilterProcessResult {
    if ($this->settings['flavor'] == 'github') {
      $converter = new GithubFlavoredMarkdownConverter([
        // Test with <em>blah</em>.
        'html_input' => 'strip',
        // Test with javascript:alert('xss')
        'allow_unsafe_links' => FALSE,
      ]);
    }
    else {
      // Standard Markdown.
      $converter = new CommonMarkConverter([
        // Test with <em>blah</em>.
        'html_input' => 'strip',
        // Test with javascript:alert('xss')
        'allow_unsafe_links' => FALSE,
      ]);
    }

    // Allow other modules to modify the configuration.
    $this->moduleHandler->invokeAll('markdown_easy_config_modify', [
      &$converter,
    ]);

    $converted = $converter->convert($text);
    return new FilterProcessResult($converted->__toString());
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE): string {
    return (string) $this->t('Parses markdown and converts it to HTML.');
  }

}
