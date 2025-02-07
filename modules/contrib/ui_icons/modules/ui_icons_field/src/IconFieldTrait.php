<?php

declare(strict_types=1);

namespace Drupal\ui_icons_field;

/**
 * Provides a trait for icon field.
 */
trait IconFieldTrait {

  /**
   * Get the icon rendering position options available to the link formatter.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup[]
   *   An array of options for position options.
   */
  public function getDisplayPositions(): array {
    return [
      'before' => $this->t('Before'),
      'after' => $this->t('After'),
      'icon_only' => $this->t('Icon only'),
    ];
  }

  /**
   * Get the icon selector options.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup[]
   *   An array of options for selectors options.
   */
  private function getPickerOptions(): array {
    return [
      'icon_autocomplete' => $this->t('Autocomplete'),
      'icon_picker' => $this->t('Picker'),
    ];
  }

}
