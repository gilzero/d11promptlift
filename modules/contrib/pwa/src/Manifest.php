<?php

namespace Drupal\pwa;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Manifest JSON building service.
 */
class Manifest implements ManifestInterface {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * The Symfony request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * The theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  private $themeManager;

  /**
   * The file entity storage.
   *
   * @var \Drupal\file\FileStorageInterface
   */
  protected $fileStorage;

  /**
   * Constructor; saves dependencies.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The Symfony request stack.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $themeManager
   *   The theme manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The file entity storage.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    ModuleHandlerInterface $moduleHandler,
    RequestStack $requestStack,
    ThemeManagerInterface $themeManager,
    EntityTypeManagerInterface $entityTypeManager,
  ) {
    $this->configFactory = $configFactory;
    $this->moduleHandler = $moduleHandler;
    $this->requestStack  = $requestStack;
    $this->themeManager  = $themeManager;
    $this->fileStorage   = $entityTypeManager->getStorage('file');
  }

  /**
   * {@inheritdoc}
   */
  public function toArray(): array {
    // Get values.
    $config = $this->configFactory->get('pwa.config');
    $httpHost = $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost();
    $modulePath = $httpHost . '/' . $this->moduleHandler->getModule('pwa')->getPath();

    // Define basic and recommended fields:
    $manifestData = [
      // Basic fields:
      'name' => $config->get('name'),
      'short_name' => $config->get('short_name'),
      'start_url' => $config->get('start_url'),
      'display' => $config->get('display'),
      'id' => $config->get('app_id'),
      // Recommended fields:
      'theme_color' => $config->get('theme_color'),
      'background_color' => $config->get('background_color'),
      'scope' => $config->get('scope'),
      'orientation' => $config->get('orientation'),
    ];

    $iconId = $config->get('image_fid');

    // Icon fallback src:
    $iconSrc = $modulePath . '/assets/icon-512.png';
    $iconSmallSrc = $modulePath . '/assets/icon-192.png';
    $iconVerySmallSrc = $modulePath . '/assets/icon-144.png';

    // If the icon file entity exists, use it and its derivatives, as the
    // App icons:
    if (!empty($iconId) && ($icon = $this->fileStorage->load($iconId)) !== NULL) {
      $iconSrc = $httpHost . $icon->createFileUrl();
      $iconSmallSrc = $httpHost . $this->fileStorage->load($config->get('image_small_fid'))->createFileUrl();
      $iconVerySmallSrc = $httpHost . $this->fileStorage->load($config->get('image_very_small_fid'))->createFileUrl();
    }

    $manifestData['icons'] = [
      0 => [
        'src' => $iconSrc,
        'sizes' => '512x512',
        'type' => 'image/png',
        'purpose' => 'any',
      ],
      1 => [
        'src' => $iconSmallSrc,
        'sizes' => '192x192',
        'type' => 'image/png',
        'purpose' => 'any',
      ],
      2 => [
        'src' => $iconVerySmallSrc,
        'sizes' => '144x144',
        'type' => 'image/png',
        'purpose' => 'any',
      ],
    ];

    // Add optional fields:
    if (!empty($description = $config->get('description'))) {
      $manifestData['description'] = $description;
    }
    if (!empty($categories = array_filter($config->get('categories')))) {
      $manifestData['categories'] = $categories;
    }
    if (!empty($lang = $config->get('lang'))) {
      $manifestData['lang'] = $lang;
    }
    if (!empty($dir = $config->get('dir'))) {
      $manifestData['dir'] = $dir;
    }

    $this->moduleHandler->alter('pwa_manifest', $manifestData);
    $this->themeManager->alter('pwa_manifest', $manifestData);

    return $manifestData;
  }

  /**
   * {@inheritdoc}
   */
  public function toJson(): string {
    $manifestData = $this->toArray();
    return Json::encode($manifestData);
  }

  /**
   * {@inheritDoc}
   */
  public function deleteImages() {
    $config = $this->configFactory->get('pwa.config');
    $imageIds = [];
    if (!empty($imageId = $config->get('image_fid'))) {
      $imageIds = [
        $imageId,
        $config->get('image_small_fid'),
        $config->get('image_very_small_fid'),
      ];
    }
    foreach ($imageIds as $imageId) {
      $imageEntity = $this->fileStorage->load($imageId);
      if ($imageEntity !== NULL) {
        $imageEntity->delete();
      }
    }
  }

}
