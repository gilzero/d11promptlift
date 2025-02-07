/**
 * @file
 * Contains definition of the behaviour Pattern.css.
 */

(function ($, Drupal, drupalSettings, once) {
  "use strict";

  Drupal.behaviors.patternCSS = {
    attach: function (context, settings) {

      const elements = drupalSettings.patterncss.elements;

      $.each(elements, function (index, element) {
        let options = {
          selector: element.selector,
          segment: element.segment ? element.segment : 'background',
          pattern:  element.pattern,
          color: element.color,
          background: element.background,
          position: element.position,
          size: element.size,
          height: element.height,
          translateX: element.translateX,
          translateY: element.translateY,
        };

        if (Array.isArray(options.selector)) {
          $.each(options.selector, function (index, selector) {
            if (once('patterncss', selector).length) {
              options.selector = selector;
              new Drupal.patternCSS(options);
            }
          });
        }
        else {
          if (once('patterncss', options.selector).length) {
            new Drupal.patternCSS(options);
          }
        }
      });

    }
  };

  Drupal.patternCSS = function (options) {
    let patterSize = '-md';
    switch (parseInt(options.size)) {
      case 1:
        patterSize = '-xs';
        break;

      case 2:
        patterSize = '-sm';
        break;

      case 3:
        patterSize = '-md';
        break;

      case 4:
        patterSize = '-lg';
        break;

      case 5:
        patterSize = '-xl';
        break;
    }

    // Build Pattern.css classes from global AdminCSS settings.
    let patternClass = `pattern-` + options.pattern + patterSize;

    if (options.pattern === 'checks' || 'zigzag') {
      $(options.selector).css({
        'color': options.color,
        'background-color': options.background,
        'background-position': '',
      });
    }
    else {
      $(options.selector).css({
        'color': options.color,
        'background-color': options.background,
        'background-position': options.position,
      });
    }

    if (options.segment == 'separator') {
      $(options.selector).css({
        'height': options.height,
      });
    }

    if (options.segment == 'text') {
      $(options.selector).addClass('text-pattern');
    }

    if (options.segment == 'shadow') {
      $(options.selector).css({
        'display': 'inline-block',
      });
      $(options.selector + ' h4').css({
        'transform': 'translate(' + options.translateX + 'px, ' + options.translateY + 'px)',
      });
    }

    if (options.segment == 'image') {
      $(options.selector).css({
        'display': 'inline-block',
      });
      $(options.selector + ' img').css({
        'transform': 'translate(' + options.translateX + 'px, ' + options.translateY + 'px)',
      });
    }

    // Add Pattern.css classes.
    $(options.selector).addClass(patternClass);
  };

})(jQuery, Drupal, drupalSettings, once);
