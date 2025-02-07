<?php

/**
 * @file
 * Update hooks.
 */

/**
 * Install hook event dispatcher 8.x-2.x submodules.
 */
function libraries_provider_update_8101() {
  \Drupal::service('module_installer')->install(['core_event_dispatcher']);
}

/**
 * Install autoservices.
 */
function libraries_provider_update_8102() {
  \Drupal::service('module_installer')->install(['autoservices']);
}
