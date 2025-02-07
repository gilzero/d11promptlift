# BACK TO TOP

## CONTENTS OF THIS FILE

 * Introduction
 * Installation
 * Configuration
 * Keyboard shortcuts
 * Upgrading
 * Maintainers


## INTRODUCTION

Back To Top adds a button that hovers in the bottom of your screen and allow users to smoothly scroll up the page. Back To Top have a nice easing effect and also checks if you interact with the page (for example, scrolling or clicking with the mouse), in which case the animation is canceled. Many other scripts lock the screen or just teleport you to the top.


## INSTALLATION

Install as you would normally install a contributed Drupal module. Visit:
https://www.drupal.org/docs/extending-drupal/installing-modules
for further information.


## CONFIGURATION

There are settings for:

 1) Preventing the button to appear in mobile or touch devices. This setting
    makes the button not appear for screen widths up to 760px.
 2) Choosing between a image och text/css button.
 3) Placement of the button.
 4) Style of the text/css button.


## KEYBOARD SHORTCUTS

Back To Top don't add keyboard shortcuts because it's build in to browsers.

 * Chrome/Safari/Firefox Mac: Command + Up Arrow
 * Chrome/Edge/Firefox Windows/Linux: Home
 * Edge Mac: Shift + Space (a screen at a time)


## UPGRADING (from 2.x to 3.x)

Use either the terminal or the UI to complete the following steps:

1. **In the UI**: Go to the **Extend > Uninstall** page (`/admin/modules/uninstall`).
    - First, uninstall the *Back to Top* module. Your settings will be preserved for when you reinstall it later.
    - Next, uninstall the *jQuery UI Effects* module.
    - Ignore the version notes suggesting any action related to the *jQuery UI* module, as it has too many dependencies of its own.
    - If you're using a module dependent on *jQuery UI*—such as *CKEditor Accordion*—update that module first.

2. **In the terminal**: Run the following command to install the version of *Back to Top* compatible with Drupal 9, 10, and 11:
   
   ```bash
   composer require 'drupal/back_to_top:^3.0'
   ```
   
   This will automatically remove the *jQuery UI Effects* module from your codebase.

3. **Return to the UI**: Go to the **Extend** page and reinstall the *Back to Top* module.
    - You'll now have version 3.0.0 without the *jQuery UI Effects* dependency.

4. **Update your database** and **clear the caches**.

When you return to the *Back to Top* configuration page, your previous settings will be intact, and you'll be one step closer to updating Drupal to version 10.x.

If you update the *Back to Top* module from 2.x to 3.x without first uninstalling *jQuery UI Effects*, you may encounter a "Mismatched entity and/or field definitions" error when attempting to update the database. In such a case, you'll need to revert to *Back to Top* version 2.0.0 and follow the steps outlined above to resolve the issue.


## MAINTAINERS

Current maintainers:
 * Mattias Axelsson (acke) (https://www.drupal.org/u/acke)
 * Sumit Madan (sumitmadan) (https://www.drupal.org/u/sumitmadan)
 * Thomas Alsén (https://www.drupal.org/u/alsén)

The drupal module Back To Top was developed by Mattias Axelsson (drupalname: acke) and sponsored by HAPPINESS WEB AGENCY (http://www.happiness.se) in the beautiful city Stockholm, Sweden. Follow me at https://x.com/mattiasaxelsson for more information.
