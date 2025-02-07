<?php

declare(strict_types=1);

namespace Drupal\mcp\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Model Context Protocol settings for this site.
 */
final class SettingsForm extends ConfigFormBase {

  /**
   * The MCP plugin manager.
   *
   * @var \Drupal\mcp\McpPluginManager
   */
  protected $pluginManagerMcp;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);

    $instance->pluginManagerMcp = $container->get('plugin.manager.mcp');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'mcp_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['mcp.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(
    array $form,
    FormStateInterface $form_state,
  ): array {
    $config = $this->config('mcp.settings');

    // General Settings container.
    $form['general'] = [
      '#type'  => 'details',
      '#title' => $this->t('General Settings'),
      '#open'  => TRUE,
    ];

    $form['general']['enable_sse'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Enable HTTP SSE'),
      '#description'   => $this->t(
        'Enable Server-Sent Events for real-time updates. This transforms Drupal into MCP server itself. Alternatively, you can use node-based Drupal MCP client to connect to the server. For more details on how this two approaches differ, check the @documentation.', [
          '@documentation' => Link::fromTextAndUrl($this->t('documentation'), Url::fromUri('https://mcp-77a54f.pages.drupalcode.org/', ['attributes' => ['target' => '_blank']]))->toString(),
        ]
      ),
      '#default_value' => $config->get('enable_sse') ?? TRUE,
    ];

    // Plugin settings wrapper.
    $form['plugins_wrapper'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];

    // Vertical tabs to group plugin config forms.
    $form['plugins_wrapper']['tabs'] = [
      '#type'       => 'vertical_tabs',
      '#title'      => $this->t('Plugin Settings'),
      '#attributes' => ['style' => 'min-height: 300px;'],
    ];

    // Get each plugin definition from the manager.
    $availableInstances = $this->pluginManagerMcp->getAvailablePlugins(TRUE);
    foreach ($availableInstances as $instance) {
      $plugin_id = $instance->getPluginId();
      $definition = $instance->getPluginDefinition();

      // Create a new details element for each plugin's sub-form.
      $form['plugins_wrapper'][$plugin_id] = [
        '#type'        => 'details',
        '#title'       => $definition['name'] ?? $instance->getPluginId(),
        '#description' => $definition['description'] ?? '',
        '#group'       => 'plugins_wrapper][tabs',
      ];

      // Build a subform for the plugin.
      $form['plugins_wrapper'][$plugin_id]['enabled'] = [
        '#type'          => 'checkbox',
        '#title'         => $this->t('Enable'),
        '#default_value' => $instance->getConfiguration()['enabled'] ?? TRUE,
      ];
      $form['plugins_wrapper'][$plugin_id]['config'] = [];
      $subformState = SubformState::createForSubform(
        $form['plugins_wrapper'][$plugin_id]['config'], $form, $form_state
      );

      // Let the plugin add its custom fields.
      $form['plugins_wrapper'][$plugin_id]['config']
        = $instance->buildConfigurationForm(
        $form['plugins_wrapper'][$plugin_id]['config'], $subformState
      );

      // If the plugin has no custom configuration options,
      // then don't show the plugin's details element at all.
      if (empty($form['plugins_wrapper'][$plugin_id]['config'])) {
        $form['plugins_wrapper'][$plugin_id]['config_message'] = [
          '#type'   => 'container',
          '#states' => [
            'visible' => [
              ':input[name="plugins_wrapper[' . $instance->getPluginId()
              . '][enabled]"]' => ['checked' => TRUE],
            ],
          ],
          'message' => [
            '#type'   => 'markup',
            '#markup' => new TranslatableMarkup(
              "No additional configuration options available."
            ),
            '#prefix' => '<p>',
            '#suffix' => '</p>',
          ],
        ];
      }
      else {
        // Wrap the plugin's config in a details element.
        $form['plugins_wrapper'][$plugin_id]['config'] = [
          '#type'   => 'details',
          '#open'   => TRUE,
          '#title'  => new TranslatableMarkup("Additional Configuration"),
          '#states' => [
            'visible' => [
              ':input[name="plugins_wrapper[' . $instance->getPluginId()
              . '][enabled]"]' => ['checked' => TRUE],
            ],
          ],
        ] + $form['plugins_wrapper'][$plugin_id]['config'];
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(
    array &$form,
    FormStateInterface $form_state,
  ): void {
    parent::validateForm($form, $form_state);

    $plugins_values = $form_state->getValue('plugins_wrapper') ?? [];
    foreach ($plugins_values as $plugin_id => $plugin_values) {
      if ($plugin_id === 'tabs') {
        continue;
      }

      $instance = $this->pluginManagerMcp->createInstance($plugin_id);
      $subformState = SubformState::createForSubform(
        $form['plugins_wrapper'][$plugin_id],
        $form,
        $form_state
      );

      // Let the plugin do custom validation if needed.
      $instance->validateConfigurationForm(
        $form['plugins_wrapper'][$plugin_id], $subformState
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(
    array &$form,
    FormStateInterface $form_state,
  ): void {
    // Load our config object for editing.
    $config = $this->config('mcp.settings');

    // Save general settings.
    $config->set(
      'enable_sse', (bool) $form_state->getValue('enable_sse')
    );

    // Save plugin settings.
    $plugins_values = $form_state->getValue('plugins_wrapper') ?? [];
    foreach ($plugins_values as $plugin_id => $plugin_values) {
      if ($plugin_id === 'tabs') {
        continue;
      }

      // Get the existing plugin instance with stored config:
      $instance = $this->pluginManagerMcp->createInstance($plugin_id);

      // Let it process its form submission.
      $subformState = SubformState::createForSubform(
        $form['plugins_wrapper'][$plugin_id],
        $form,
        $form_state
      );
      $instance->submitConfigurationForm(
        $form['plugins_wrapper'][$plugin_id],
        $subformState
      );

      // Now $instance->configuration is updated.
      // Write it back into our module config array.
      $config->set("plugins.$plugin_id", $instance->getConfiguration());
    }

    // Save everything to mcp.settings.
    $config->save();

    // Call parent submit to show success message, etc.
    parent::submitForm($form, $form_state);
  }

}
