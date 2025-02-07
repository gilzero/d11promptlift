<?php

/**
 * @file
 * Containing module specific hook implementation examples.
 */

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\node\Entity\Node;

/**
 * Alters cached urls list for the service worker file.
 *
 * This hook allows adding or removing urls to the whitelist for the cache urls.
 * This is the same as the "URLs to cache on install" list on the service worker
 * admin page.
 *
 * After you make your modifications you do NOT need to return the results.
 * Since $cacheUrls is passed by reference, any changes made to $cacheUrls are
 * automatically registered.
 *
 * @param array &$cacheUrls
 *   List of urls to cache.
 * @param \Drupal\Core\Cache\CacheableMetadata $cacheableMetadata
 *   Cacheability metadata.
 */
function hook_pwa_service_worker_cache_urls_alter(&$cacheUrls, CacheableMetadata &$cacheableMetadata) {
  // Get a node URL and its cacheability metadata.
  $generated_url = Node::load('1')->toUrl()->toString(TRUE);
  // Add the URL to the list.
  $cacheUrls[] = $generated_url->getGeneratedUrl();
  // Merge the cacheability metadata.
  $cacheableMetadata = $cacheableMetadata->merge($generated_url);
}

/**
 * Alters excluded urls list for the service worker file.
 *
 * This hook allows adding or removing urls to the blacklist for the excluded
 * urls. This is the same as the "URLs to cache on install" list on the service
 * worker admin page.
 *
 * After you make your modifications you do NOT need to return the results.
 * Since $excludeUrls is passed by reference, any changes made to $excludeUrls
 * are automatically registered.
 *
 * @param array &$excludeUrls
 *   List of url to cache. Paths matching these patterns will not be cached by
 *   the Service Worker. Can be JavaScript regex.
 * @param \Drupal\Core\Cache\CacheableMetadata $cacheableMetadata
 *   Cacheability metadata.
 */
function hook_pwa_service_worker_exclude_urls_alter(&$excludeUrls, CacheableMetadata &$cacheableMetadata) {
  $excludeUrls[] = "path/pattern/.*";
  $excludeUrls[] = "just/path";
}

/**
 * Alters cached assets urls list for the service worker file.
 *
 * This hook allows altering the list of asset URLs to cache on install.
 *
 * @param array &$resources
 *   List of asset URLs to cache.
 *
 * @see hook_pwa_service_worker_cache_urls_assets_page_alter()
 */
function hook_pwa_service_worker_cache_urls_assets_alter(&$resources) {
  $module_path = \Drupal::service('extension.list.module')->getPath('pwa_service_worker');
  $resources[] = "/$module_path/assets/loading.gif";
}

/**
 * Alters cached assets urls list for each page for the service worker file.
 *
 * This hook allows altering the list of asset URLs to cache on install. This
 * hook is called once for every page to be cached.
 *
 * @param array &$resources
 *   List of asset URLs to cache for the given page.
 * @param string $page
 *   The relative URL of the page that resources are currently being extracted
 *   from.
 * @param \DOMXPath $xpath
 *   The page xpath that may be queried to add additional resource URLs.
 *
 * @see hook_pwa_service_worker_cache_urls_assets_alter()
 */
function hook_pwa_service_worker_cache_urls_assets_page_alter(&$resources, $page, $xpath) {
  // Cache lazily-loaded images.
  foreach ($xpath->query('//img[@data-src]') as $image) {
    $resources[] = $image->getAttribute('data-src');
  }
}
