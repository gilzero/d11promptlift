<?php

namespace Drupal\drulma_companion\Hook;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Add container class when drulma or any theme base on drulma is installed.
 */
class AddContainerClass {

  /**
   * Constructs a new AddContainerClass instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $themeHandler
   *   The theme handler service.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected ThemeHandlerInterface $themeHandler,
  ) {
  }

  /**
   * Implements hook_themes_installed().
   *
   * @param array $themeList
   *   Array containing the names of the themes being installed.
   */
  #[Hook('themes_installed')]
  public function addClass(array $themeList): void {
    foreach ($themeList as $theme) {
      // The installed theme is drulma or has drulma as a base theme.
      if ($theme === 'drulma' || in_array('drulma', array_keys($this->themeHandler->getTheme($theme)->base_themes ?? []), TRUE)) {
        foreach ([
          'branding',
          'footer',
          'powered',
          'messages',
        ] as $blockId) {
          /** @var \Drupal\block\Entity\Block $block */
          $block = $this->entityTypeManager->getStorage('block')->load($theme . '_' . $blockId);
          if ($block) {
            $third_party_settings = $block->get('third_party_settings');
            if (!$third_party_settings) {
              $third_party_settings = [
                'block_class' => [
                  'classes' => 'container',
                ],
              ];
              $block->set('third_party_settings', $third_party_settings);
              $dependencies = $block->get('dependencies');
              $dependencies['module'][] = 'block_class';
              $block->set('dependencies', $dependencies);
              $block->save();
            }
          }
        }
      }
    }
  }

}
