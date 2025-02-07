<?php

namespace Drupal\menu_item_fields_ui\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    foreach ([
      'entity.field_config.menu_link_content_field_edit_form',
      'entity.field_config.menu_link_content_storage_edit_form',
      'entity.field_config.menu_link_content_field_delete_form',
      'entity.menu_link_content.field_ui_fields',
      'field_ui.field_storage_config_add_menu_link_content',
      'field_ui.field_storage_config_reuse_menu_link_content',
      'entity.entity_form_display.menu_link_content.default',
      'entity.entity_form_display.menu_link_content.form_mode',
      'entity.entity_view_display.menu_link_content.default',
      'entity.entity_view_display.menu_link_content.view_mode',
      // Field group module does not detect the bundle of the entity.
      'field_ui.field_group_add_menu_link_content.display',
      'field_ui.field_group_add_menu_link_content.display.view_mode',
      'field_ui.field_group_add_menu_link_content.form_display',
      'field_ui.field_group_add_menu_link_content.form_display.form_mode',
      'field_ui.field_group_delete_menu_link_content.display',
      'field_ui.field_group_delete_menu_link_content.display.view_mode',
      'field_ui.field_group_delete_menu_link_content.form_display',
      'field_ui.field_group_delete_menu_link_content.form_display.form_mode',
    ] as $routeName) {
      if ($route = $collection->get($routeName)) {
        $route->setDefault('bundle', 'menu_link_content');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() : array {
    $events = parent::getSubscribedEvents();
    // Trigger after field_group.
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', -220];
    return $events;
  }

}
