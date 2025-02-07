<?php

declare(strict_types=1);

namespace Drupal\layout_options_ui\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Select the templates to override their class with the layout_options class.
 */
class Settings extends ConfigFormBase {

  /**
   * The layout plugin manager.
   *
   * @var \Drupal\Core\Layout\LayoutPluginManager
   */
  protected $layoutPluginManager;

  /**
   * The plugin cache clear object.
   *
   * @var \Drupal\Core\ProxyClass\Plugin\CachedDiscoveryClearer
   */
  protected $pluginCacheClearer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    $instance = parent::create($container);
    $instance->pluginCacheClearer = $container->get('plugin.cache_clearer');
    $instance->layoutPluginManager = $container->get('plugin.manager.core.layout');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layout_options_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'layout_options.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('layout_options.settings');
    /** @var array $settings */
    $settings = $config->get('layout_overrides') ?? [];

    $header = [
      'provider' => $this->t('Provider'),
      'layout_id' => $this->t('Layout Id'),
    ];
    $options = $this->getLayouts($settings);

    $form['info'] = [
      '#markup' => $this->t(
        '<p>Select the layouts that should use the layout_options Class
 instead of the default Class.</p>
 <p>NOTE: Submitting this form will clear the plugin cache.</p>'
      ),
    ];

    $form['layout_overrides'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
      '#empty' => $this->t('No compatible layouts found'),
      '#default_value' => $settings ? $settings : [],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    /** @var array $settings */
    $settings = $form_state->getValue('layout_overrides', []);
    $overrides = [];
    foreach ($settings as $key => $value) {
      $overrides[$key] = $value === 0 ? 0 : 1;
    }
    $this->configFactory->getEditable('layout_options.settings')
      ->set('layout_overrides', $overrides)
      ->save();

    // Clear all plugin caches.
    $this->pluginCacheClearer->clearCachedDefinitions();
    $this->messenger()->addMessage($this->t('Plugin cache cleared.'));

    parent::submitForm($form, $form_state);
  }

  /**
   * Create the options list of layouts that can be overridden.
   *
   * @return array
   *   Returns an array of layouts with provider and layout id values.
   */
  public function getLayouts(array $settings) {
    $layouts = [];
    /** @var \Drupal\Core\Layout\LayoutDefinition[] $definitions */
    $definitions = $this->layoutPluginManager->getDefinitions();

    foreach ($definitions as $key => $definition) {
      $provider = $definition->getProvider();
      $providerKey = "{$provider}__{$key}";
      if ($definition->getClass() === 'Drupal\Core\Layout\LayoutDefault') {
        $layouts[$providerKey] = [
          'provider' => $provider,
          'layout_id' => $key,
        ];
      }
      // Show overridden layouts.
      elseif (isset($settings[$providerKey]) && $settings[$providerKey]) {
        $layouts[$providerKey] = [
          'provider' => $provider,
          'layout_id' => $key,
        ];
      }
    }
    return $layouts;
  }

}
