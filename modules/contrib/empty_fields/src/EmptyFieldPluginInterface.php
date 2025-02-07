<?php

namespace Drupal\empty_fields;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines an interface for empty fields.
 *
 * @see \Drupal\empty_fields\Annotation\EmptyField
 * @see \Drupal\empty_fields\EmptyFieldPluginBase
 * @see \Drupal\empty_fields\EmptyFieldsPluginManager
 * @see plugin_api
 */
interface EmptyFieldPluginInterface {

  /**
   * Builds render array for empty field.
   *
   * @param array $context
   *   An associative array containing:
   *   - entity: The entity being rendered.
   *   - view_mode: The view mode; for example, 'full' or 'teaser'.
   *   - display: The EntityDisplay holding the display options.
   *
   * @var array
   *   Renderable array to display.
   */
  public function react(array $context);

  /**
   * Returns the configuration form elements specific to this plugin.
   *
   * Plugins that need to add form elements to the configuration
   * form should implement this method.
   *
   * @param array $form
   *   The form definition array for the block configuration form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The renderable form array representing the entire configuration form.
   *
   * @see \Drupal\Core\Field\FormatterInterface::settingsForm()
   */
  public function settingsForm(array $form, FormStateInterface $form_state);

  /**
   * Returns a short summary line for the current formatter settings.
   *
   * @return string
   *   Text for the field formatter settings summary.
   *
   * @see \Drupal\Core\Field\FormatterInterface::settingsSummary()
   */
  public function settingsSummary();

}
