<?php

namespace Drupal\rebuild_cache_access\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Returns responses for rebuild_cache_access module routes.
 */
class RebuildCacheAccessController extends ControllerBase {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public function __construct(RequestStack $request_stack, ModuleHandlerInterface $module_handler) {
    $this->requestStack = $request_stack;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('module_handler')
    );
  }

  /**
   * Reload the previous page.
   */
  public function reloadPage() {
    $request = $this->requestStack->getCurrentRequest();
    if ($request->server->get('HTTP_REFERER')) {
      return $request->server->get('HTTP_REFERER');
    }
    else {
      return '/';
    }
  }

  /**
   * Rebuild all caches, then redirects to the previous page.
   */
  public function rebuildCache() {
    if ($this->moduleHandler->moduleExists('views')) {
      views_invalidate_cache();
    }
    drupal_flush_all_caches();
    $this->messenger()->addMessage($this->t('All caches cleared.'));
    return new RedirectResponse($this->reloadPage());
  }

}
