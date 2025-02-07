<?php

/**
 * @file
 * Containing module specific hook implementation examples.
 */

/**
 * Alters manifest data.
 *
 * This hook allows altering the generated manifest data before encoding it to
 * JSON.
 *
 * @param array &$manifestData
 *   Manifest data generated in Manifest::toArray().
 */
function hook_pwa_manifest_alter(&$manifestData) {
  $manifestData['short_name'] = 'App';
}
