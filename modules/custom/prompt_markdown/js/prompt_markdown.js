(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.promptMarkdown = {
    attach: function (context, settings) {
      once('markdown-processed', '.markdown-field', context).forEach(function(element) {
        const content = element.innerHTML;
        // Configure marked options
        marked.setOptions({
          breaks: true,
          gfm: true,
          headerIds: true
        });
        
        try {
          // Remove any HTML entities first
          const decodedContent = $('<div/>').html(content).text();
          const htmlContent = marked.parse(decodedContent);
          element.innerHTML = htmlContent;
        } catch (e) {
          console.error('Markdown parsing failed:', e);
        }
      });
    }
  };
})(jQuery, Drupal, once);
