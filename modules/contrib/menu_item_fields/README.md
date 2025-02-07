# Description

The main purpose of the module is to be able to add fields to
custom menu items and render them with different view modes.

## Installation and usage

First enable the `menu_item_fields_ui` module since is
needed to create the fields and view modes.

In order to render a menu with the configured fields
a new block is provided with the module called "Menu with fields".

After the menu is built the UI module can be disabled 
in production just like the `field_ui` module.

## Features

* Add fields to menu items and sort their display.
* Have different form modes for each menu. Create and enable a form mode with the same machine name as the menu.
* Show fielded menu items with a block chosing the view mode.
* Configure additional options on the formatter like the `rel` and `target` attributes.
* Add more attributes with the [Link attributes](https://www.drupal.org/project/link_attributes/) module.
* Optionally have a field on the menu link entity that overrides the display mode for each item.
  * Is up to the site builder to create it.
  * This field needs to store the string value of the display mode, for example: 'mega'.
  * The field that stores the view mode is configured on the block.

## Similar modules

* [Menu item extras](https://www.drupal.org/project/menu_item_extras):
  * Provides a bundle for each menu while this module does not add any new bundle.
  * With Menu item extras the children output can be sorted on the "Manage display" interface. On Menu Item Content Fields the children are below the parent since the template tries to be as close as possible to Drupal core but you can override it.
  * In general this module tries to be more simple trying to override as few templates as posible.

## Future improvements

Being able to load the field information into other kind of menu items so
all the menu items can be rendered similarly (e.g with icons) and not only custom menu items.

If you believe that fields on menu items are a good feature to have in Drupal core
there is [a proposal to make it so](https://www.drupal.org/project/ideas/issues/3047131)
since is just enabling an interface.

## Notes

Inspired by the [Menu Link Content fields](https://www.drupal.org/project/menu_link_content_fields) module
and of course Menu Link Extras.
