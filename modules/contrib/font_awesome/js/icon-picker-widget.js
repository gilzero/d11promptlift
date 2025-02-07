/**
 * @file
 * Font Awesome icon picker behavior.
 */

(function (Drupal, once) {

  'use strict';

  /**
   * Attach the icon picker widget.
   */
  Drupal.behaviors.fontAwesomeIconPicker = {
    attach: function (context, settings) {
      function whenAvailable(name, callback) {
        var interval = 100;
        window.setTimeout(function() {
          if (window[name]) {
            callback(window[name]);
          } else {
            whenAvailable(name, callback);
          }
        }, interval);
      };
      whenAvailable('IconPicker', function(IconPicker) {
        var inputs = once('font-awesome-icon-picker', '.font-awesome-icon-picker', context);
        // In the layout builder context the preview is not possible.
        // See https://www.drupal.org/project/drupal/issues/2952390
        for (let i = 0; i < inputs.length; i++) {
          if (document.querySelector(`#drupal-off-canvas #${inputs[i].id}`) === null) {
            var preview = document.createElement('i');
            var previewId = `${inputs[i].id}-icon-picker-preview`
            preview.id = previewId;
            preview.className = inputs[i].value;

            var previewContainer = document.createElement('div');
            previewContainer.className = 'font-awesome-icon-picker-preview';
            previewContainer.appendChild(preview);
            inputs[i].parentNode.insertBefore(previewContainer, inputs[i]);
          }

          var button = document.createElement('button');
          var buttonId = `${inputs[i].id}-icon-picker-button`
          button.id = buttonId;
          button.className = 'font-awesome-icon-picker-button';
          button.formNoValidate = true;
          button.type = 'button';
          button.dataset.iconpickerInput = `#${inputs[i].id}`
          button.dataset.iconpickerPreview = `#${previewId}`
          button.appendChild(document.createTextNode(Drupal.t('Select Icon')));

          inputs[i].parentNode.insertBefore(button, inputs[i]);

          IconPicker.Init({
            jsonUrl: inputs[i].dataset.jsonUrl,
            searchPlaceholder: Drupal.t('Search Icon'),
            showAllButton: Drupal.t('Show All'),
            cancelButton: Drupal.t('Cancel'),
            noResultsFound: Drupal.t('No results found.'),
            borderRadius: '20px',
          });
          IconPicker.Run(`#${buttonId}`);
        }
      });
    }
  };

} (Drupal, once));
