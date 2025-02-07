# UI Suite USWDS

## Contents of this file

- Introduction
- How it works
  - Components implemented with UI Patterns
  - Utilities implemented with UI Styles
- Requirements
- Installation
- Configuration
  - Creating a subtheme
- Maintainers
- Shout-out

## Introduction

A site-builder friendly [USWDS](https://designsystem.digital.gov/) theme for Drupal, using the [UI Suite](https://www.drupal.org/project/ui_suite).
Use USWDS parts (components, helpers, utilities and layouts) directly from
Drupal backend interface (layout builder, manage display, views, blocks, etc.)
in a low-code way.

## How it works

![Overview](docs/images/schema.png)

### Components implemented with [UI Patterns](https://www.drupal.org/project/ui_patterns)

Each component is a folder in /components/.

You can browse the pattern libraries directly inside Drupal: /admin/appearance/ui/components/ui_suite_uswds;
for example, the 'card' pattern is available here: /admin/appearance/ui/components/ui_suite_uswds/card.

Thanks to the ui_patterns ecosystem, patterns are automatically available
directly for site building in many Drupal entities.

### Utilities implemented with [UI Styles](https://www.drupal.org/project/ui_styles)

Utilities are implemented as styles in ui_suite_uswds.ui_styles.yml

You can browse the styles libraries directly inside Drupal: /styles.

The styles are automatically available for site building inside layout builder's
components (blocks) & sections (layouts).

## Requirements

This theme requires Drupal core >= 10.3.

- [UI Styles](https://www.drupal.org/project/ui_styles)
- [Layout Options](https://www.drupal.org/project/layout_options)

Also needs [USWDS Library](https://github.com/uswds/uswds) >= 3.11.0

## Installation

Install as you would normally install a contributed Drupal theme. See
https://www.drupal.org/node/1897420 for further information.

## Configuration

Theme configuration page can be located at `admin/appearance/settings/ui_suite_uswds`

Here you can specify several settings.

* CDN settings
  * Recommended to use custom approach see How to set up Subtheme.
  * Can change which version of USWDS to load.
* Header settings
  * Determine which header style to use. Note a menu has to be in the primary menu region in block layout.
    * Extended see [USWDS Extended header](https://designsystem.digital.gov/components/header/#extended-header)
    * Basic see [USWDS Basic header](https://designsystem.digital.gov/components/header/#basic-header)
  * `Use megamenu in the header?`
    * This determines styling that applies to the menu
    * DOES NOT alter how the menu renders. So if the menu block is configured to only show 1 level that's what will
      happen.
  * `Megamenu: Display second level as headers`
    * Requires previous check to be active.
  * `Display the official U.S. government banner at the top of each page.`
    * Will toggle very top accordion of banner at the top.
* Footer settings
  * Determine which footer style to use.
    * DOES NOT alter how the menu renders. So if the menu block is configured to only show 1 level that's what will
      happen.
    * Big (default) see [USWDS Big footer](https://designsystem.digital.gov/components/footer/#big-footer)
    * Slim see [USWDS Slim footer](https://designsystem.digital.gov/components/footer/#slim-footer)
    * Did not add Medium as it's very similar to big minus the menu levels. I didn't want the theme
      to alter menu rendering, so left that control in the menu block.
  * Various fields for agency info.
  * Various social media links that will appear at the bottom corner.
* Menu Settings
  * By default, menu blocks being in certain regions they get picked up and auto theme suggestions. With USWDS markup
    * `Bypass USWDS menu processing for these menus.` allows for turning this feature off.

### Creating a subtheme

1. Go to `admin/appearance/settings/ui_suite_uswds`.
2. Scroll to the bottom and open Subtheme section.
3. Fill in the fields and click 'Create'
   1. This will create a subtheme in the `web/themes/custom` folder.
4. In the subtheme there are several npm scripts that can be edited based on project needs.
5. At minium can run `npm run build` which will pull USWDS library and build following [USWDS Compile](https://github.com/uswds/uswds-compile)
   1. This is when you would want to turn off CDN settings.
6. Subtheme is ready to use and recommend reading up on how to use custom settings in USWDS
   1. Example https://designsystem.digital.gov/documentation/settings/

## Maintainers

Current maintainers:
- Stephen Mustgrave ([@smustgrave](https://www.drupal.org/u/smustgrave))

## Shout-out

The developers of [USWDS Base](https://www.drupal.org/project/uswds_base)
- Initial work for templates forked from that theme, with maintainers permission.
The developers of [Bootstrap5](https://www.drupal.org/project/bootstrap5)
- Forked the subtheme generator, with maintainers permission.
