# Readmore

This small module provides a field formatter that displays a text field
as trimmed text with *Read more* / *Read less* links.

## Installation

### With Composer (recommended)

Navigate to [your preferred release](https://www.drupal.org/project/readmore/releases), and then run the displayed *Install with Composer* command.

### Without Composer

Place the module in your Drupal modules directory and enable it in `admin/modules`.

## Usage

### Views Configuration

To use in Views, do the following:

1. Click on one of the fields in your view to configure it.
1. Change the Formatter to `Readmore`.
1. Change or set the various options.
1. Hit the Apply button to save.

### Entity configuration

TODO

## Notes

At the time of this writing...

* The module only works on Long Text fields, not (short) Text ones.
* While running in Views' Edit/Preview mode, the *Read more* link doesn't stick as it opens and then quickly closes again. However, it works fine outside of Edit/Preview mode.

Patches or funding to fix these or other issues are welcome.
