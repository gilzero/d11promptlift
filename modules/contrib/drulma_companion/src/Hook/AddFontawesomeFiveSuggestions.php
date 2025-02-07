<?php

namespace Drupal\drulma_companion\Hook;

use Drupal\Core\Asset\LibraryDiscoveryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\file\Entity\File;
use Drupal\file\IconMimeTypes;

/**
 * Add suggestions for Fontawesome 5.
 */
class AddFontawesomeFiveSuggestions {

  const TEMPLATE_SUFFIX = '__fa5';

  /**
   * Constructs a new AddFontawesomeFiveSuggestions instance.
   *
   * @param \Drupal\Core\Asset\LibraryDiscoveryInterface $libraryDiscovery
   *   The library discovery service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   */
  public function __construct(
    protected LibraryDiscoveryInterface $libraryDiscovery,
    protected ModuleHandlerInterface $moduleHandler,
  ) {
  }

  /**
   * Implements hook_theme_suggestions_alter().
   *
   * @param array $suggestions
   *   An array of alternate, more specific names for template files.
   * @param array $variables
   *   An array of variables passed to the theme hook, passed by reference.
   * @param string $hook
   *   The base hook name.
   */
  #[Hook('theme_suggestions_alter')]
  public function addSuggestions(array &$suggestions, array &$variables, $hook): void {
    if (
      !in_array($hook, [
        'feed_icon',
        'input',
        'select',
        'file_link',
      ]) ||
      !$this->isFontawesomeFiveEnabled()
    ) {
      return;
    }
    foreach ($suggestions as $suggestion) {
      $suggestions[] = $suggestion . self::TEMPLATE_SUFFIX;
    }
    $suggestions[] = $hook . self::TEMPLATE_SUFFIX;

    $type = $variables['element']['#type'] ?? '';
    $type_suggestion = $hook . '__' . $type . self::TEMPLATE_SUFFIX;
    if ($type && !in_array($type_suggestion, $suggestions, TRUE)) {
      // Handle case likes type = date and actual type of the input = time.
      $subtype = $variables['element']['#attributes']['type'] ?? '';
      if ($subtype && $subtype != $type) {
        $type_suggestion = $hook . '__' . $type . '_' . $subtype . self::TEMPLATE_SUFFIX;
      }

      $suggestions[] = $type_suggestion;
    }
    $name = $variables['element']['#attributes']['name'] ?? '';
    if ($name) {
      // Remove any non-word character from the name.
      $name = preg_replace('~[\W]~', '', $name);
      $suggestions[] = $hook . '__' . $name . self::TEMPLATE_SUFFIX;
    }
    $formId = $variables['element']['#form_id'] ?? '';
    if ($formId && $type) {
      $suggestions[] = $hook . '__' . $type . '__' . $formId . self::TEMPLATE_SUFFIX;
    }

    // Add the value of the submit as a suggestion.
    if (
      $type === 'submit' &&
      $variables['element']['#value'] instanceof TranslatableMarkup
    ) {
      $untranslatedCleanString = strtolower(preg_replace('~[\W]~', '', $variables['element']['#value']->getUntranslatedString()));
      $suggestions[] = $variables['theme_hook_original'] . '__' . $untranslatedCleanString . self::TEMPLATE_SUFFIX;
    }

    if (
      isset($variables['file']) &&
      $variables['file'] instanceof File
    ) {
      $cleanMimeType = strtolower(preg_replace('~[\W]~', '', IconMimeTypes::getIconClass($variables['file']->getMimeType())));
      $suggestions[] = $hook . '__' . $cleanMimeType . self::TEMPLATE_SUFFIX;
    }
  }

  /**
   * Determine when the fontawesome library is loaded.
   */
  protected function isFontawesomeFiveEnabled(): bool {
    if (!$this->moduleHandler->moduleExists('lp_fontawesome')) {
      return FALSE;
    }
    $libraries = $this->libraryDiscovery->getLibrariesByExtension('lp_fontawesome');
    if (!$libraries) {
      return FALSE;
    }
    foreach (['fontawesome', 'fontawesomesvg'] as $libraryName) {
      if (
        empty($libraries[$libraryName]) ||
        empty($libraries[$libraryName]['libraries_provider']['enabled'] ||
        version_compare($libraries[$libraryName]['version'], '5.0.0') < 0)
      ) {
        continue;
      }
      return TRUE;
    }
    return FALSE;
  }

}
