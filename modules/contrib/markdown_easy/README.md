## Introduction

The Markdown Easy module is a Drupal text filter to convert Markdown into HTML.

The primary use case for this module is to quickly and easily be able to
configure and utilize a Markdown text filter. This module utilizes the
[league/commonmark Markdown parser library](https://commonmark.thephpleague.com)
and allows for the bare minimum configuration in the Drupal admin interface.

It is strongly suggested to utilize the "Limit allowed HTML tags and correct
faulty HTML" and "Convert line breaks into HTML" Drupal core text filters in
conjunction with this module.

## Requirements

None (other than the desire to convert Markdown to HTML!)

## Installation

Install as you would normally install a contributed Drupal module.
See: https://www.drupal.org/node/895232 for further information.

## Configuration
### Automatic
- A new "Markdown" text format is created when Markdown Easy is enabled. This
text format may be customized as desired.
### Manual
If you do not want to use the "Markdown" text format that is automatically
created when Markdown Easy is enable, then use the following steps as a starting
point to create or modify your own text format:
- Add the Markdown Easy text filter to any text format.
- Select "Standard Markdown" or "GitHub-flavored Markdown" in the text filter's
settings on the text format's configuration page.
- IMPORTANT - Enable and configure the "Limit allowed HTML tags and correct
faulty HTML" filter to run after the Markdown Easy filter. Without this step,
the text format will allow all HTML tags.
- IMPORTANT - Enable and configure the "Convert line breaks into HTML" filter
to run after the Markdown Easy filter and the "Limit allowed HTML tags and
correct faulty HTML" filter.
- For best results, at a minimum, text formats utilizing the Markdown Easy
- filter should be configured as follows:

![Screenshot of the suggested filter order](https://www.drupal.org/files/markdown-easy-filter-order.png)

- Markdown Easy requires (via validation) that it be configured with both the
"Convert line breaks into HTML" and "Limit allowed HTML tags and correct faulty
HTML" filters enabled to run after Markdown Easy. This can be overridden (at
your peril) by removing the validation handler. See
tests/modules/markdown_easy_test/markdown_easy_test.module for an example.
- Markdown Easy requires (via validation) that the "Convert line breaks into
HTML" filter be run after the "Limit allowed HTML tags and correct faulty HTML"
for best results. This can be overridden (at your peril) by removing the
validation handler. See the Advanced configuration documentation for more
information.

## Additional information
- The Markdown Easy text filter is configured to run with the following
security-related settings by default:
  - html_input: strip
  - allow_unsafe_links: false
- See https://commonmark.thephpleague.com/2.4/security/ for more info. To
override these settings, or to customize the configuration of the Markdown
processor, utilize hook_markdown_easy_config_modify().
- Note that the Markdown Easy module is currently not compatible with the
Smart Trim module.

## Tip for migrating from Markdown module
- The [Markdown module](https://www.drupal.org/project/markdown) allows the
site-builder to install multiple Markdown processors, therefore, prior to
installing Markdown Easy, if the league/commonmark parser is already installed,
it should be removed (as Markdown Easy uses the latest version and Markdown does
not).

composer remove league/commonmark

## Documentation
- Full documentation at https://www.drupal.org/docs/extending-drupal/contributed-modules/contributed-module-documentation/markdown-easy

## Maintainers

Current maintainers for Drupal 10:

- Michael Anello (ultimike) - https://www.drupal.org/u/ultimike
