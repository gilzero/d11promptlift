<?php

namespace Drupal\pwa;

/**
 * Manifest JSON building service.
 */
interface ManifestInterface {

  /**
   * Build the manifest array  based on the configuration.
   *
   * @return array
   *   Manifest array data.
   */
  public function toArray(): array;

  /**
   * Retrieve the manifest data as a json.
   *
   * @return string
   *   Manifest json data.
   */
  public function toJson(): string;

  /**
   * Deletes the images that are used for the manifest file.
   */
  public function deleteImages();

}
