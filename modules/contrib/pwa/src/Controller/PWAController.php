<?php

namespace Drupal\pwa\Controller;

use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\pwa\ManifestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Default controller for the pwa module.
 */
class PWAController implements ContainerInjectionInterface {

  /**
   * The manifest service.
   *
   * @var \Drupal\pwa\ManifestInterface
   */
  private $manifest;

  /**
   * PWAController constructor.
   *
   * @param \Drupal\pwa\ManifestInterface $manifest
   *   The manifest service.
   */
  public function __construct(ManifestInterface $manifest) {
    $this->manifest = $manifest;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('pwa.manifest'),
    );
  }

  /**
   * Fetches the manifest data.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The manifest file as a response object.
   */
  public function manifestData() {
    $response = new CacheableResponse($this->manifest->toJson(), 200, [
      'Content-Type' => 'application/json',
    ]);
    $meta_data = $response->getCacheableMetadata();
    $meta_data->addCacheTags(['manifestjson']);
    $meta_data->addCacheContexts(['languages:language_interface']);
    return $response;
  }

}
