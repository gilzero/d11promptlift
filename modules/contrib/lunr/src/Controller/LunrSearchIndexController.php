<?php

namespace Drupal\lunr\Controller;

use Drupal\Core\Url;
use Drupal\lunr\LunrSearchInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for Lunr search indexing.
 */
class LunrSearchIndexController extends ControllerBase {

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Constructs a LunrSearchIndexController object.
   *
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The file system.
   */
  public function __construct(FileSystemInterface $fileSystem) {
    $this->fileSystem = $fileSystem;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('file_system')
    );
  }

  /**
   * Provides a page to trigger the indexing process.
   *
   * @param \Drupal\lunr\LunrSearchInterface $lunr_search
   *   The Lunr search entity.
   *
   * @return array
   *   A render array.
   */
  public function page(LunrSearchInterface $lunr_search) {
    $build = [];

    $view = $lunr_search->getView();

    if (!$view) {
      $this->messenger()->addError('A view has not been configured for this search.');
      return $build;
    }

    $paths = [];
    $upload_paths = [];
    foreach ($this->languageManager()->getLanguages() as $language) {
      $paths[] = $view->getUrl()->setOption('language', $language)->toString();
      $upload_paths[] = Url::fromRoute('entity.lunr_search.upload_index', [
        'lunr_search' => $lunr_search->id(),
      ], ['language' => $language])->toString();
    }

    $build['#attached']['library'][] = 'lunr/index.form';
    $build['#attached']['drupalSettings']['lunr']['indexSettings'][$lunr_search->id()] = [
      'paths' => $paths,
      'uploadPaths' => $upload_paths,
      'usePager' => $view->getPager()->usePager(),
      'indexFields' => $lunr_search->getIndexFields(),
      'displayField' => $lunr_search->getDisplayField(),
    ];

    $build['description'] = [
      '#markup' => '<p>' . $this->t('Submitting this form will create a new Lunr index based on the results of the View located at @path.', [
        '@path' => $view->getUrl()->toString(),
      ]) . '</p>',
    ];

    $build['progress'] = [
      '#markup' => '<p class="lunr-search-index-progress"></p>',
    ];

    $build['index'] = [
      '#type' => 'button',
      '#attributes' => [
        'class' => ['js-lunr-search-index-button'],
        'data-lunr-search' => $lunr_search->id(),
      ],
      '#value' => $this->t('Index'),
    ];

    CacheableMetadata::createFromObject($lunr_search)->applyTo($build);

    return $build;
  }

  /**
   * Provides an upload route for index pages.
   *
   * @param \Drupal\lunr\LunrSearchInterface $lunr_search
   *   The Lunr search entity.
   * @param string $page
   *   The page number.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  public function uploadPage(LunrSearchInterface $lunr_search, $page, Request $request) {
    $filename = str_replace('PAGE', $page, $lunr_search->getDocumentPathPattern());
    $directory = dirname($filename);
    $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);
    $this->fileSystem->saveData($request->getContent(), $filename, FileSystemInterface::EXISTS_REPLACE);
    return new Response('');
  }

  /**
   * Provides an upload route for when the index is complete.
   *
   * @param \Drupal\lunr\LunrSearchInterface $lunr_search
   *   The Lunr search entity.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  public function upload(LunrSearchInterface $lunr_search, Request $request) {
    $filename = $lunr_search->getIndexPath();
    $directory = dirname($filename);
    $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);
    $this->fileSystem->saveData($request->getContent(), $filename, FileSystemInterface::EXISTS_REPLACE);
    $lunr_search->setLastIndexTime(time());
    return new Response('');
  }

}
