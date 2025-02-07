<?php

namespace Drupal\pwa_service_worker\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Service Worker configuration form.
 */
class ServiceWorkerConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pwa_service_worker_configuration_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['pwa_service_worker.config'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('pwa_service_worker.config');

    $form['urls_to_cache'] = [
      '#type' => 'textarea',
      '#title' => $this->t('URLs to cache on install'),
      '#description' => $this->t('These will serve the page offline even if they have not been visited. Define one per line. Examples would be "/" for everything, or "/myCachedSite".<br>Make sure the URL is not a 404. Make sure these are relative URLs, tokens or regex are not supported. Because we cache these, you may need to flush your cache when changing this value.'),
      '#default_value' => $config->get('urls_to_cache'),
    ];

    $form['urls_to_exclude'] = [
      '#type' => 'textarea',
      '#title' => $this->t('URLs to exclude'),
      '#description' => $this->t('Takes regexes. These URLs will use network-only, default config should be, <code>admin/.*</code> and <code>user/reset/.*</code>.'),
      '#default_value' => $config->get('urls_to_exclude'),
    ];

    // @todo This setting is quite mysterious, the default path "/offline" is
    // also used in the "pwa_service_worker.phone_home" route.
    $form['offline_page'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Offline page'),
      '#default_value' => $config->get('offline_page') ?: '/offline',
      '#size' => 40,
      '#description' => $this->t('This page is displayed when the user is offline and the requested page is not cached. It is automatically added to the "URLs to cache". Use <code>/offline</code> for a generic "You are offline" page.'),
    ];

    $form['cache_version'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cache version'),
      '#description' => $this->t('Changing this number will invalidate all Service Worker caches. Use it when assets have significantly changed or if you want to force a cache refresh for all clients.'),
      '#size' => 5,
      '#default_value' => $config->get('cache_version') ?: 1,
    ];

    $form['skip_waiting'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Skip waiting'),
      '#description' => $this->t("If enabled, an updated service worker will not wait, but instead activates as soon as it is installed."),
      '#title_display' => 'after',
      '#default_value' => $config->get('skip_waiting'),
    ];

    return parent::buildForm($form, $form_state);

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    // Check urls format.
    $urls_to_cache = pwa_str_to_list($form_state->getValue('urls_to_cache'));
    foreach ($urls_to_cache as $page) {
      // If link is internal.
      try {
        $url = Url::fromUserInput($page);
      }
      catch (\Exception $e) {
        $form_state->setErrorByName('urls_to_cache', $this->t("The user-entered URL '@page' must begin with a '/', '?', or '#'.", ['@page' => $page]));
      }
      // If link does not exist.
      if (isset($url) && !$url->isRouted()) {
        $form_state->setErrorByName('urls_to_cache', $this->t('Error "@page" URL to Cache is a 404.', ['@page' => $page]));
      }
    }

    parent::validateForm($form, $form_state);

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->config('pwa_service_worker.config');

    // Save new config data.
    $config
      ->set('urls_to_cache', $form_state->getValue('urls_to_cache'))
      ->set('urls_to_exclude', $form_state->getValue('urls_to_exclude'))
      ->set('offline_page', $form_state->getValue('offline_page'))
      ->set('cache_version', $form_state->getValue('cache_version'))
      ->set('skip_waiting', $form_state->getValue('skip_waiting'))
      ->save();

    parent::submitForm($form, $form_state);

  }

}
