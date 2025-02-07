/**
 * @file
 * Basic prism settings.
 */

(function (Drupal) {
  'use strict';

  Drupal.behaviors.prism = {
    attach: function(context, settings) {
      // Trigger prism to highlight things on the page again after an AJAX
      // operation has completed.
      if (context instanceof HTMLElement) {
        Prism.highlightAll();
      }
    }
  };

})(Drupal);
