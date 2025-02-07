<?php

namespace Drupal\lunr\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\lunr\LunrSearchInterface;

/**
 * Delivers responses for Lunr search pages.
 */
class LunrSearchController extends ControllerBase {

  /**
   * Title callback for the search form.
   *
   * @param \Drupal\lunr\LunrSearchInterface $lunr_search
   *   The Lunr search entity.
   *
   * @return array
   *   The render array.
   */
  public function page(LunrSearchInterface $lunr_search) {
    $build = [];

    $build['form'] = [
      '#type' => 'form',
      '#form_id' => 'lunr_search_form',
      '#id' => Html::getUniqueId('lunr-search-form'),
      '#method' => 'GET',
      '#attributes' => [
        'class' => [
          'lunr-search-page-form',
          'js-lunr-search-page-form',
        ],
        'data-lunr-search' => $lunr_search->id(),
      ],
    ];

    $id = Html::getUniqueId('search');
    $build['form']['input'] = [
      '#type' => 'search',
      '#title' => $this->t('Keywords'),
      '#id' => $id,
      '#name' => 'search',
      '#attributes' => [
        'class' => [
          'js-lunr-search-input',
        ],
      ],
    ];

    $build['form']['submit'] = [
      '#type' => 'submit',
      '#name' => '',
      '#value' => $this->t('Search'),
      '#attributes' => [
        'class' => [
          'js-lunr-search-submit',
        ],
      ],
      '#weight' => 1,
    ];

    $build['results'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'js-lunr-search-results',
          'lunr-search-results',
        ],
      ],
    ];

    $build['#attached']['library'][] = 'lunr/search';
    $build['#attached']['drupalSettings']['lunr']['searchSettings'][$lunr_search->id()] = [
      'indexPath' => \Drupal::service('file_url_generator')->transformRelative(\Drupal::service('file_url_generator')->generateAbsoluteString($lunr_search->getIndexPath()) . '?v=' . $lunr_search->getLastIndexTime()),
      'documentPathPattern' => \Drupal::service('file_url_generator')->transformRelative(\Drupal::service('file_url_generator')->generateAbsoluteString($lunr_search->getDocumentPathPattern()) . '?v=' . $lunr_search->getLastIndexTime()),
      'displayField' => $lunr_search->getDisplayField(),
      'resultsPerPage' => $lunr_search->getResultsPerPage(),
      'id' => $lunr_search->id(),
    ];
    $suffix = '?' . \Drupal::state()->get('system.css_js_query_string', '0');
    $build['#attached']['drupalSettings']['lunr']['workerPath'] = base_path() . \Drupal::service('extension.list.module')->getPath('lunr') . '/js/search.worker.js' . $suffix;
    $build['#attached']['drupalSettings']['lunr']['lunrPath'] = base_path() . \Drupal::service('extension.list.module')->getPath('lunr') . '/js/vendor/lunr/lunr.min.js' . $suffix;

    CacheableMetadata::createFromObject($lunr_search)->applyTo($build);

    $build['#cache']['tags'][] = 'lunr_last_index_time:' . $lunr_search->id();

    $this->moduleHandler()->alter('lunr_search_page', $build, $lunr_search);

    return $build;
  }

  /**
   * Title callback for the search form.
   *
   * @param \Drupal\lunr\LunrSearchInterface $lunr_search
   *   The Lunr search entity.
   *
   * @return string
   *   The title for the page.
   */
  public function title(LunrSearchInterface $lunr_search) {
    return $lunr_search->label();
  }

  /**
   * Determines access for the search page.
   *
   * @param \Drupal\lunr\LunrSearchInterface $lunr_search
   *   The Lunr search entity.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(LunrSearchInterface $lunr_search) {
    $index_exists = file_exists($lunr_search->getIndexPath());
    if (!$index_exists && $this->currentUser()->hasPermission('administer lunr search')) {
      $this->messenger()->addWarning($this->t('The search index does not exist. To create a new index, <a href=":index">click here</a>.', [
        ':index' => Url::fromRoute('entity.lunr_search.index', [
          'lunr_search' => $lunr_search->id(),
        ])->toString(),
      ]));
    }
    return AccessResult::allowedIf($index_exists);
  }

}
