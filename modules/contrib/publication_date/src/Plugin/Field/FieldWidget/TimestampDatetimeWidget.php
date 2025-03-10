<?php

namespace Drupal\publication_date\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'datetime timestamp' widget.
 *
 * @FieldWidget(
 *   id = "publication_date_timestamp",
 *   label = @Translation("Datetime Timestamp"),
 *   field_types = {
 *     "published_at"
 *   }
 * )
 */
class TimestampDatetimeWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    if (isset($items[$delta]->value)) {
      $default_value = DrupalDateTime::createFromTimestamp($items[$delta]->value);
    }
    else {
      $default_value = '';
    }
    $element['value'] = $element + [
      '#type' => 'datetime',
      '#default_value' => $default_value,
      '#date_year_range' => '1902:2037',
    ];
    $element['value']['#description'] = $this->t('Leave blank to use the time of form submission.');

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $values = array_filter($values, function ($item) {
      return isset($item['value']);
    });
    foreach ($values as &$item) {
      $date = NULL;
      // @todo The structure is different whether access is denied or not, to
      //   be fixed in https://www.drupal.org/node/2326533.
      if (isset($item['value']) && $item['value'] instanceof DrupalDateTime) {
        $date = $item['value'];
      }
      elseif (isset($item['value']['object']) && $item['value']['object'] instanceof DrupalDateTime) {
        $date = $item['value']['object'];
      }
      if ($date) {
        $item['value'] = $date->getTimestamp();
      }
    }
    return $values;
  }

}
