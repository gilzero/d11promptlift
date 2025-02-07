<?php

namespace Drupal\pwa\Form;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\RequestContext;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Drupal\file\FileStorageInterface;
use Drupal\file\FileUsage\FileUsageInterface;
use Drupal\pwa\ManifestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Manifest configuration form.
 */
class ManifestConfigurationForm extends ConfigFormBase {

  /**
   * The cache tags invalidator service.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * The file entity storage.
   *
   * @var \Drupal\file\FileStorageInterface
   */
  protected $fileStorage;

  /**
   * The file system helper service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The file usage backend.
   *
   * @var \Drupal\file\FileUsage\FileUsageInterface
   */
  protected $fileUsage;

  /**
   * The manifest service.
   *
   * @var \Drupal\pwa\ManifestInterface
   */
  protected $manifest;

  /**
   * The stream wrapper manager.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface
   */
  protected $streamWrapperManager;

  /**
   * The file url generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * The request context.
   *
   * @var \Drupal\Core\Routing\RequestContext
   */
  protected $requestContext;

  /**
   * Constructor; saves dependencies.
   *
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cacheTagsInvalidator
   *   The cache tags invalidator service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The factory for configuration objects.
   * @param \Drupal\file\FileStorageInterface $fileStorage
   *   The file entity storage.
   * @param \Drupal\file\FileUsage\FileUsageInterface $fileUsage
   *   The file usage backend.
   * @param \Drupal\pwa\ManifestInterface $manifest
   *   The manifest service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface $streamWrapperManager
   *   The stream wrapper manager.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $fileUrlGenerator
   *   The file url generator.
   * @param \Drupal\Core\Language\LanguageManager $languageManager
   *   The language manager service.
   * @param \Drupal\Core\Routing\RequestContext $request_context
   *   The request context.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface|null $typedConfigManager
   *   The typed config manager.
   */
  public function __construct(
    CacheTagsInvalidatorInterface $cacheTagsInvalidator,
    ConfigFactoryInterface $configFactory,
    FileStorageInterface $fileStorage,
    FileUsageInterface $fileUsage,
    ManifestInterface $manifest,
    MessengerInterface $messenger,
    StreamWrapperManagerInterface $streamWrapperManager,
    FileUrlGeneratorInterface $fileUrlGenerator,
    LanguageManager $languageManager,
    RequestContext $request_context,
    ?TypedConfigManagerInterface $typedConfigManager = NULL,
  ) {
    parent::__construct($configFactory, $typedConfigManager);
    $this->cacheTagsInvalidator = $cacheTagsInvalidator;
    $this->fileStorage          = $fileStorage;
    $this->fileUsage            = $fileUsage;
    $this->manifest             = $manifest;
    // \Drupal\Core\Messenger\MessengerTrait::messenger() defaults to getting
    // the messenger service via the static \Drupal::messenger() method if
    // $this->messenger has not been set, and so is not 100% true dependency
    // injection unless we save it here for it to find.
    $this->messenger            = $messenger;
    $this->streamWrapperManager = $streamWrapperManager;
    $this->fileUrlGenerator     = $fileUrlGenerator;
    $this->languageManager      = $languageManager;
    $this->requestContext       = $request_context;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('cache_tags.invalidator'),
      $container->get('config.factory'),
      $container->get('entity_type.manager')->getStorage('file'),
      $container->get('file.usage'),
      $container->get('pwa.manifest'),
      $container->get('messenger'),
      $container->get('stream_wrapper_manager'),
      $container->get('file_url_generator'),
      $container->get('language_manager'),
      $container->get('router.request_context'),
      $container->get('config.typed') ?? NULL,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pwa_manifest_configuration_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['pwa.config'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('pwa.config');

    $form['manifest_path_mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Add the manifest.json to specific pages'),
      '#options' => [
        'all_except_listed' => $this->t('Every page except the listed pages'),
        'listed_only' => $this->t('The listed pages only'),
      ],
      '#default_value' => $config->get('manifest_path_mode'),
    ];
    $form['manifest_paths'] = [
      '#type' => 'textarea',
      '#title_display' => 'invisible',
      '#default_value' => $config->get('manifest_paths'),
      '#description' => $this->t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. Example paths are '/page' for a specific page and '/page/*' for every path descending from '/page/' (not including '/page' itself). '<front>' is the front page.<br>Note, if left empty, the manifest.json gets added on every page."),
      '#rows' => 10,
    ];

    $form['name'] = [
      "#type" => 'textfield',
      '#title' => $this->t('Application name'),
      '#description' => $this->t('The full name of the application.<br>See the w3c definition <a href="@link" target="_blank">here</a>.', ['@link' => 'https://w3c.github.io/manifest/#name-member-0']),
      '#default_value' => $config->get('name'),
      '#required' => TRUE,
    ];

    $form['short_name'] = [
      "#type" => 'textfield',
      "#title" => $this->t('Application short name'),
      "#description" => $this->t('The short application name, which gets displayed on the users home-screen.<br>See the w3c definition <a href="@link" target="_blank">here</a>.', ['@link' => 'https://w3c.github.io/manifest/#short_name-member-0']),
      '#default_value' => $config->get('short_name'),
    ];

    $form['app_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Application ID'),
      '#default_value' => $config->get('app_id') ?? $this->getBaseUrlWithoutProtocol(),
      '#description' => $this->t('Enter the unique App ID. Defaults to the site URL without the protocol.'),
    ];

    $form['start_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Start URL'),
      '#description' => $this->t('The preferred URL that should be loaded when the user launches the web application.<br>Note, that you can either use an relative or absolute path here (e.g. "/mypwa" or "https://example.com".<br>See the w3c definition <a href="@link" target="_blank">here</a>.', ['@link' => 'https://w3c.github.io/manifest/#start_url-member']),
      '#default_value' => $config->get('start_url'),
      '#required' => TRUE,
    ];

    $form['scope'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Scope'),
      '#description' => $this->t('The navigation scope of this web applications application context. If the user navigates outside the scope, it reverts to a normal web page inside a browser tab or window.<br>See the w3c definition <a href="@link" target="_blank">here</a>.', ['@link' => 'https://w3c.github.io/manifest/#scope-member']),
      '#default_value' => $config->get('scope'),
      '#required' => TRUE,
    ];

    $form['lang'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Language'),
      '#description' => $this->t('The primary language of this web application (e.g. "en", "en-GB", "de-DE").<br>See the w3c definition <a href="@link" target="_blank">here</a>.', ['@link' => 'https://w3c.github.io/manifest/#lang-member']),
      '#default_value' => $config->get('lang'),
      '#required' => FALSE,
    ];

    $form['dir'] = [
      "#type" => 'radios',
      "#title" => $this->t('Text direction'),
      "#description" => $this->t('Defines the language specific text direction of the "name", "short-name" and "description" members.<br>See the w3c definition <a href="@link" target="_blank">here</a>.', ['@link' => 'https://w3c.github.io/manifest/#dir-member']),
      '#options' => [
        'auto' => "Auto (Default) - No explicit directionality",
        'ltr' => "Left-to-right text",
        'rtl' => "Right-to-left text",
      ],
      '#default_value' => $config->get('dir'),
      '#required' => FALSE,
    ];

    $form['orientation'] = [
      "#type" => 'radios',
      "#title" => $this->t('Orientation'),
      "#description" => $this->t('Defines the default orientation for all the websites top-level browsing contexts.<br>See the w3c definition <a href="@link" target="_blank">here</a>.', ['@link' => 'https://w3c.github.io/manifest/#orientation-member']),
      '#options' => [
        'any' => "Any (Default) - The app can be rotated by the user to any orientation allowed by the device.",
        'natural' => "Natural - The app will be viewed in the most natural orientation for the device.",
        'landscape' => "Landscape - The app will be viewed in an rotatable landscape view.",
        'landscape-primary' => "Landscape Primary - The app will be viewed in a locked landscape view using the device's natural landscape screen orientation.",
        'landscape-secondary' => "Landscape Secondary - The app will be viewed in a locked landscape view using the OPPOSITE of the device's natural landscape screen orientation.",
        'portrait' => "Portrait - The app will be viewed in an rotatable portrait view.",
        'portrait-primary' => "Portrait Primary - The app will be viewed in a locked portrait view using the device's natural landscape screen orientation.",
        'portrait-secondary' => "Portrait Secondary - The app will be viewed in a locked portrait view using the OPPOSITE of the device's natural landscape screen orientation.",
      ],
      '#default_value' => $config->get('orientation'),
      '#required' => TRUE,
    ];

    $form['categories'] = [
      "#type" => 'textfield',
      "#title" => $this->t('Categories'),
      "#description" => $this->t('The categories of your web application (divided by ","). For a list of known categories, click <a href=@link>here</a>.', ['@link' => 'https://www.w3.org/TR/manifest-app-info/#categories-member']),
      '#default_value' => implode(',', $config->get('categories')),
    ];

    $form['description'] = [
      "#type" => 'textfield',
      "#title" => $this->t('Description'),
      "#description" => $this->t('The description of your web application.<br>See the w3c definition <a href="@link" target="_blank">here</a>.', ['@link' => 'https://w3c.github.io/manifest/#description-member']),
      '#default_value' => $config->get('description'),
    ];

    $form['theme_color'] = [
      "#type" => 'color',
      "#title" => $this->t('Theme color'),
      "#description" => $this->t('Defines the default theme color for the application. Note, that this color sometimes affects how the OS displays the site.<br>See the w3c definition <a href="@link" target="_blank">here</a>.', ['@link' => 'https://w3c.github.io/manifest/#theme_color-member']),
      '#default_value' => $config->get('theme_color'),
      '#required' => TRUE,
    ];

    $form['background_color'] = [
      "#type" => 'color',
      "#title" => $this->t('Background color'),
      "#description" => $this->t('This color gets shown as the background when the application is launched.<br>See the w3c definition <a href="@link" target="_blank">here</a>.', ['@link' => 'https://w3c.github.io/manifest/#background_color-member']),
      '#default_value' => $config->get('background_color'),
      '#required' => TRUE,
    ];

    $form['display'] = [
      "#type" => 'select',
      "#title" => $this->t('Display type'),
      "#description" => $this->t('This determines which UI elements from the OS are displayed.<br>See the w3c definition <a href="@link" target="_blank">here</a>.', ['@link' => 'https://w3c.github.io/manifest/#display-member']),
      "#options" => [
        'fullscreen' => $this->t('fullscreen'),
        'standalone' => $this->t('standalone'),
        'minimal-ui' => $this->t('minimal-ui'),
        'browser' => $this->t('browser'),
      ],
      '#default_value' => $config->get('display'),
      '#required' => TRUE,
    ];

    $form['cross_origin'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('The site is behind HTTP basic authentication'),
      '#description' => $this->t('This will ensure any login credentials are passed to the manifest.'),
      '#default_value' => $config->get('cross_origin'),
    ];

    $form['icons'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Icon'),
    ];

    $imageFid = !empty($config->get('image_fid')) ? [$config->get('image_fid')] : [];
    $form['icons']['image_fid'] = [
      '#title' => $this->t('Upload app icon'),
      '#type' => 'managed_file',
      '#description' => $this->t('This image is your application icon (png files only, format: 512x512, transparent background, padding 20-30%). The padding is needed, so the icons are not getting cropped on Android phone Home-Screens (To verify an Icon, you can visit <a href="@link">Maskable App</a>).<br><strong>Note</strong>, that this uses the icons in "pwa/assets" as a fallback, if no icon is uploaded.', ['@link' => 'https://maskable.app/']),
      '#upload_validators' => [
        'FileExtension' => ['extensions' => 'png'],
        'FileImageDimensions' => ['maxDimensions' => '512x512', 'minDimensions' => '512x512'],
      ],
      '#default_value' => $imageFid,
      '#upload_location' => 'public://pwa/',
    ];

    if (!empty($imageFid)) {
      $imageId = reset($imageFid);
      $imageFile = $this->fileStorage->load($imageId);
      if ($imageFile !== NULL) {
        $imagePreviewPath = $imageFile->createFileUrl();
        $form['icons']['current_image'] = [
          '#markup' => '<label>Current App Icon:</label><br><img src="' . $imagePreviewPath . '" width="200"/>',
          '#name' => 'current image',
          '#id' => 'current_image',
        ];
      }

    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->config('pwa.config');

    // Get the categories into an array format:
    $categoriesString = $form_state->getValue('categories');
    $categoriesArray = array_map('trim', explode(',', $categoriesString));

    // Save new config data.
    $config
      ->set('name', $form_state->getValue('name'))
      ->set('short_name', $form_state->getValue('short_name'))
      ->set('app_id', $form_state->getValue('app_id'))
      ->set('orientation', $form_state->getValue('orientation'))
      ->set('categories', $categoriesArray)
      ->set('theme_color', $form_state->getValue('theme_color'))
      ->set('background_color', $form_state->getValue('background_color'))
      ->set('description', $form_state->getValue('description'))
      ->set('display', $form_state->getValue('display'))
      ->set('start_url', $form_state->getValue('start_url'))
      ->set('scope', $form_state->getValue('scope'))
      ->set('lang', $form_state->getValue('lang'))
      ->set('dir', $form_state->getValue('dir'))
      ->set('cross_origin', $form_state->getValue('cross_origin'))
      ->set('manifest_path_mode', $form_state->getValue('manifest_path_mode'))
      ->set('manifest_paths', $form_state->getValue('manifest_paths'));

    $imageFormValue = $form_state->getValue('image_fid');
    // $ImageId is always expected as int:
    $imageId = (int) reset($imageFormValue);

    // If a new image is uploaded, we need to delete the old ones:
    if ($config->get('image_fid') !== $imageId) {
      $this->manifest->deleteImages();
    }
    // Save image if exists.
    if (!empty($imageId)) {
      $imageFile = $this->fileStorage->load($imageId);
      // Return early, if the image file doesn't exist anymore:
      if ($imageFile === NULL) {
        parent::submitForm($form, $form_state);
        return;
      }
      $imageFile->setPermanent();
      $imageFile->save();

      // Note, that this will throw an error when checking for file usage on
      // the given file. But this is a core issue. See
      // https://www.drupal.org/project/drupal/issues/3187396
      $this->fileUsage->add($imageFile, 'pwa', 'pwa.config', 'icons');
      $publicScheme = $this->streamWrapperManager->getViaScheme('public');
      $pwaDirectory = $publicScheme->realpath() . '/pwa/';
      $filePath = $pwaDirectory . $imageFile->getFilename();

      // For image_small_fid.
      $newSize = 192;
      $oldSize = 512;

      $src = imagecreatefrompng($filePath);
      $dst = imagecreatetruecolor($newSize, $newSize);

      // Make transparent background.
      $color = imagecolorallocatealpha($dst, 0, 0, 0, 127);
      imagefill($dst, 0, 0, $color);
      imagesavealpha($dst, TRUE);

      imagecopyresampled($dst, $src, 0, 0, 0, 0, $newSize, $newSize, $oldSize, $oldSize);
      $imageCopyPath = $pwaDirectory . pathinfo($imageFile->getFilename(), PATHINFO_FILENAME) . '_192x192.png';
      $stream = fopen($imageCopyPath, 'w+');
      if ($stream == TRUE) {
        imagepng($dst, $stream);
        // Programmatically create the small image file entity:
        $imageCopy = $this->fileStorage->create([
          'filename' => basename($imageCopyPath),
          'uri' => 'public://pwa/' . basename($imageCopyPath),
          'status' => 1,
          'uid' => 1,
        ]);
        $imageCopy->setPermanent();
        $imageCopy->save();

        // Note, that this will throw an error when checking for file usage on
        // the given file. But this is a core issue. See
        // https://www.drupal.org/project/drupal/issues/3187396
        $this->fileUsage->add($imageCopy, 'pwa', 'pwa.config', 'icons');
        $config->set('image_small_fid', $imageCopy->id());
      }

      // For image_very_small_fid.
      $newSize = 144;

      $src = imagecreatefrompng($filePath);
      $dst = imagecreatetruecolor($newSize, $newSize);

      // Make transparent background.
      $color = imagecolorallocatealpha($dst, 0, 0, 0, 127);
      imagefill($dst, 0, 0, $color);
      imagesavealpha($dst, TRUE);

      imagecopyresampled($dst, $src, 0, 0, 0, 0, $newSize, $newSize, $oldSize, $oldSize);
      $imageCopyPath = $pwaDirectory . pathinfo($imageFile->getFilename(), PATHINFO_FILENAME) . '_144x144.png';
      if ($stream = fopen($imageCopyPath, 'w+')) {
        imagepng($dst, $stream);
        // Programmatically create the very small image file entity:
        $imageCopy = $this->fileStorage->create([
          'filename' => basename($imageCopyPath),
          'uri' => 'public://pwa/' . basename($imageCopyPath),
          'status' => 1,
          'uid' => 1,
        ]);
        $imageCopy->setPermanent();
        $imageCopy->save();

        // Note, that this will throw an error when checking for file usage on
        // the given file. But this is a core issue. See
        // https://www.drupal.org/project/drupal/issues/3187396
        $this->fileUsage->add($imageCopy, 'pwa', 'pwa.config', 'icons');
        $config->set('image_very_small_fid', $imageCopy->id());
      }
    }
    $config->set('image_fid', $imageId)->save();

    $this->cacheTagsInvalidator->invalidateTags(['manifestjson']);

    parent::submitForm($form, $form_state);
  }

  /**
   * Helper function to get the base URL without the protocol.
   */
  private function getBaseUrlWithoutProtocol() {
    $base_url = $this->requestContext->getCompleteBaseUrl();
    $parsed_url = parse_url($base_url);
    return $parsed_url['host'] . ($parsed_url['path'] ?? '');
  }

}
