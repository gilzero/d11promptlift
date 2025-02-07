<?php

declare(strict_types=1);

namespace Drupal\ui_suite_bootstrap\HookHandler;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\filter\FilterFormatInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add tabs on filter tips.
 */
class PreprocessFilterTips implements ContainerInjectionInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected CurrentRouteMatch $currentRouteMatch;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected AccountProxyInterface $currentUser;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   The current route match.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   */
  public function __construct(
    CurrentRouteMatch $currentRouteMatch,
    AccountProxyInterface $currentUser,
  ) {
    $this->currentRouteMatch = $currentRouteMatch;
    $this->currentUser = $currentUser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('current_route_match'),
      $container->get('current_user')
    );
  }

  /**
   * Get all filter format tips as tabs and tab content.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocess(array &$variables): void {
    /** @var \Drupal\filter\FilterFormatInterface|null $current_format */
    $current_format = $this->currentRouteMatch->getParameter('filter_format');
    $current_format_id = $current_format ? $current_format->id() : FALSE;

    $build = [
      '#type' => 'component',
      '#component' => 'ui_suite_bootstrap:nav',
      '#props' => [
        'variant' => 'tabs',
        'items' => [],
      ],
      '#slots' => [
        'tab_content' => [],
      ],
      '#attributes' => [
        'class' => [
          'mb-3',
        ],
      ],
    ];

    foreach (\filter_formats($this->currentUser) as $format_id => $format) {
      // Set the current format ID to the first format.
      if (!$current_format_id) {
        $current_format_id = $format_id;
      }
      $active = $current_format_id === $format_id;

      $build['#props']['items'][] = $this->getTab($format, $active);
      $build['#slots']['tab_content'][] = $this->getPane($format);
    }

    $variables['tips'] = $build;
  }

  /**
   * Get a tab.
   *
   * @param \Drupal\filter\FilterFormatInterface $format
   *   The filter format.
   * @param bool $active
   *   If the tab should be active or not.
   *
   * @return array
   *   The tab element.
   */
  protected function getTab(FilterFormatInterface $format, bool $active): array {
    return [
      'title' => $format->label(),
      'url' => Url::fromRoute('filter.tips', [
        'filter_format' => $format->id(),
      ])->toString(),
      'link_attributes' => [
        'class' => [
          $active ? 'active' : '',
        ],
      ],
    ];
  }

  /**
   * Get a pane.
   *
   * @param \Drupal\filter\FilterFormatInterface $format
   *   The filter format.
   *
   * @return array
   *   The pane element.
   */
  protected function getPane(FilterFormatInterface $format): array {
    $tips = [];
    // Iterate over each format's enabled filters.
    /** @var \Drupal\filter\FilterPluginCollection $filters */
    $filters = $format->filters();
    foreach ($filters->getAll() as $filter) {
      // Ignore filters that are not enabled.
      if (!$filter->status) {
        continue;
      }

      $tip = $filter->tips(TRUE);
      if (isset($tip)) {
        $tips[] = ['#markup' => $tip];
      }
    }

    return [
      '#theme' => 'item_list',
      '#items' => $tips,
    ];
  }

}
