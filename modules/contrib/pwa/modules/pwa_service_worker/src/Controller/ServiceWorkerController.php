<?php

namespace Drupal\pwa_service_worker\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Url;
use Drupal\pwa\ManifestInterface;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Default controller for the pwa_service_worker module.
 */
class ServiceWorkerController implements ContainerInjectionInterface {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * The Guzzle HTTP client instance.
   *
   * @var \GuzzleHttp\Client
   */
  private $httpClient;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManager;

  /**
   * The module extension list.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   *
   * @see https://www.drupal.org/project/drupal/issues/2940481
   *   This service is currently still marked as @internal as of Drupal core
   *   9.2.x, but will hopefully be stabilized and no longer be @internal soon.
   */
  private $moduleExtensionList;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * The theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  private $themeManager;

  /**
   * The manifest service.
   *
   * @var \Drupal\pwa\ManifestInterface
   */
  private $manifest;

  /**
   * Constructor; saves dependencies.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   * @param \GuzzleHttp\Client $httpClient
   *   The Guzzle HTTP client instance.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   * @param \Drupal\Core\Extension\ModuleExtensionList $moduleExtensionList
   *   The module extension list.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $themeManager
   *   The theme manager.
   * @param \Drupal\pwa\ManifestInterface $manifest
   *   The manifest service.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    Client $httpClient,
    LanguageManagerInterface $languageManager,
    ModuleExtensionList $moduleExtensionList,
    ModuleHandlerInterface $moduleHandler,
    ThemeManagerInterface $themeManager,
    ManifestInterface $manifest,
  ) {
    $this->configFactory       = $configFactory;
    $this->httpClient          = $httpClient;
    $this->languageManager     = $languageManager;
    $this->moduleExtensionList = $moduleExtensionList;
    $this->moduleHandler       = $moduleHandler;
    $this->themeManager        = $themeManager;
    $this->manifest            = $manifest;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('http_client'),
      $container->get('language_manager'),
      $container->get('extension.list.module'),
      $container->get('module_handler'),
      $container->get('theme.manager'),
      $container->get('pwa.manifest'),
    );
  }

  /**
   * Fetch all resources.
   *
   * @param array $pages
   *   The page URL.
   *
   * @return array
   *   Returns an array of the CSS and JS file URLs.
   */
  public function fetchOfflinePageResources($pages) {

    // For each Drupal path, request the HTML response and parse any CSS/JS
    // found within the HTML. Since this is the pure HTML response, any DOM
    // modifications that trigger new requests cannot be accounted for. An
    // example would be an asynchronously-loaded webfont.
    $resources = [];

    foreach ($pages as $page) {
      try {
        // URL is validated as internal in ConfigurationForm.php.
        $url = Url::fromUserInput($page, ['absolute' => TRUE])->toString(TRUE);
        $url_string = $url->getGeneratedUrl();
        $response = $this->httpClient->get(
          $url_string, ['headers' => ['Accept' => 'text/plain']]
        );

        $data = $response->getBody();
        if (empty((string) $data)) {
          continue;
        }
      }
      catch (\Exception $e) {
        continue;
      }

      $page_resources = [];

      // Get all DOM data.
      $dom = new \DOMDocument();
      @$dom->loadHTML($data);

      $xpath = new \DOMXPath($dom);
      foreach ($xpath->query('//script[@src]') as $script) {
        $page_resources[] = $script->getAttribute('src');
      }
      foreach ($xpath->query('//link[@rel="stylesheet"][@href]') as $stylesheet) {
        $page_resources[] = $stylesheet->getAttribute('href');
      }
      foreach ($xpath->query('//style[@media="all" or @media="screen"]') as $stylesheets) {
        preg_match_all(
          "#(/(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)#i",
          ' ' . $stylesheets->textContent,
          $matches
        );
        $page_resources = array_merge($page_resources, $matches[0]);
      }
      foreach ($xpath->query('//img[@src]') as $image) {
        $page_resources[] = $image->getAttribute('src');
      }

      // Allow other modules and themes to alter cached asset URLs for this
      // page.
      $this->moduleHandler->alter('pwa_service_worker_cache_urls_assets_page', $page_resources, $page, $xpath);
      $this->themeManager->alter('pwa_service_worker_cache_urls_assets_page', $page_resources, $page, $xpath);

      $resources = array_merge($resources, $page_resources);
    }

    $dedupe = array_unique($resources);
    $dedupe = array_values($dedupe);
    // Allow other modules to alter the final list of cached asset URLs.
    $this->moduleHandler->alter('pwa_service_worker_cache_urls_assets', $dedupe);
    return $dedupe;
  }

  /**
   * Replaces service-worker file data with variables from the Drupal config.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object.
   *
   * @return \Drupal\Core\Cache\CacheableResponse
   *   The content of the service-worker file.
   */
  public function serviceWorkerRegistration(Request $request) {
    $path = $this->moduleExtensionList->getPath('pwa_service_worker');

    $sw = file_get_contents($path . '/js/serviceworker.js');

    // Get module configuration.
    $swConfig = $this->configFactory->get('pwa_service_worker.config');
    $pwaConfig = $this->configFactory->get('pwa.config');

    // Get URLs from config.
    $cacheUrls = pwa_str_to_list($swConfig->get('urls_to_cache'));
    $cacheUrls[] = $swConfig->get('offline_page');
    $exclude_cache_url = pwa_str_to_list($swConfig->get('urls_to_exclude'));

    // Initialize a CacheableMetadata object.
    $cacheable_metadata = new CacheableMetadata();
    $cacheable_metadata
      ->addCacheableDependency($pwaConfig)
      ->addCacheableDependency($swConfig)
      ->setCacheMaxAge(86400)
      ->setCacheContexts(['url']);

    // Get icons list and convert into array of sources.
    $manifest = $this->manifest->toArray();
    $cacheIcons = [];
    if (!empty($manifest['icons'])) {
      foreach ($manifest['icons'] as $icon) {
        $cacheIcons[] = $icon['src'];
      }
    }

    // Combine URLs from admin UI with manifest icons.
    $cacheWhitelist = array_merge($cacheUrls, $cacheIcons);

    // Allow other modules to alter the URL's. Also pass the CacheableMetadata
    // object so these modules can add cacheability metadata to the response.
    $this->moduleHandler->alter('pwa_service_worker_cache_urls', $cacheWhitelist, $cacheable_metadata);
    $this->moduleHandler->alter('pwa_service_worker_exclude_urls', $exclude_cache_url, $cacheable_metadata);

    // Active languages on the site.
    $languages = $this->languageManager->getLanguages();

    // Get the skip-waiting setting.
    $skip_waiting = $swConfig->get('skip_waiting') ? 'true' : 'false';

    // Set up placeholders.
    // @todo Simply pass these values through the drupal settings js api,
    // instead of replacing values by code comment names, which is quite dirty:
    $replace = [
      '[/*cacheUrls*/]' => Json::encode($cacheWhitelist),
      '[/*activeLanguages*/]' => Json::encode(array_keys($languages)),
      '[/*exclude_cache_url*/]' => Json::encode($exclude_cache_url),
      "'/offline'/*offlinePage*/" => "'" . $swConfig->get('offline_page') . "'",
      '[/*modulePath*/]' => '/' . $this->moduleHandler->getModule('pwa_service_worker')->getPath(),
      '1/*cacheVersion*/' => '\'' . $this->getCacheVersion() . '\'',
      'false/*pwaSkipWaiting*/' => $skip_waiting,
    ];
    if (!empty($cacheUrls)) {
      $replace['[/*cacheUrlsAssets*/]'] = Json::encode($this->fetchOfflinePageResources($cacheUrls));
    }

    // @todo This isn't specified in the api docs and should also prefixed with
    // "pwa_service_worker":
    $this->moduleHandler->alter('pwa_replace_placeholders', $replace);

    // Fill placeholders and return final file.
    $data = str_replace(array_keys($replace), array_values($replace), $sw);

    // @todo This isn't specified in the api docs.
    $this->moduleHandler->alter('pwa_service_worker_data', $data);

    $response = new CacheableResponse($data, 200, [
      'Content-Type' => 'application/javascript',
      'Service-Worker-Allowed' => $pwaConfig->get('scope'),
    ]);
    $response->addCacheableDependency($cacheable_metadata);

    return $response;
  }

  /**
   * Returns current cache version.
   *
   * @return string
   *   Cache version.
   */
  public function getCacheVersion() {
    // Get module configuration.
    $config = $this->configFactory->get('pwa_service_worker.config');

    // Look up module release from package info.
    $pwa_module_info = $this->moduleExtensionList->getExtensionInfo('pwa');
    $pwa_module_version = $pwa_module_info['version'];

    // Packaging script will always provide the published module version.
    // Checking for NULL is only so maintainers have something predictable to
    // test against.
    if ($pwa_module_version == NULL) {
      $pwa_module_version = '2.x-dev';
    }

    return $pwa_module_version . '-v' . ($config->get('cache_version') ?: 1);
  }

  /**
   * Phone home uninstall.
   *
   * @package Applied from patch
   * https://www.drupal.org/project/pwa/issues/2913023#comment-12819311.
   */
  public function moduleActivePage() {
    return [
      '#tag' => 'h1',
      '#value' => 'PWA module is installed.',
      '#attributes' => [
        'data-drupal-pwa-active' => TRUE,
      ],
    ];
  }

  /**
   * Provide a render array for offline pages.
   *
   * @return array
   *   The render array.
   */
  public function offlinePage() {
    return [
      '#theme' => 'offline',
    ];
  }

}
