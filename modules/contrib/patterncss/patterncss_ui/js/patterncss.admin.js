/**
 * @file
 * Contains definition of the behaviour Pattern.css.
 */

(function ($, Drupal, drupalSettings, once) {
  "use strict";

  Drupal.behaviors.patternCSS = {
    attach: function (context, settings) {

      const example = drupalSettings.patterncss.sample;
      const Selector = example.selector;
      const undefined = void 0;

      if (once('pattern__sample', Selector).length) {
        // Pattern.css preview replay.
        $(once('pattern__replay', '.pattern__replay', context)).on(
          'click',
          function (event) {
            let options = {
              selector: '.sample__' + $('#edit-segment').val(),
              segment: $('#edit-segment').val(),
              pattern: $('#edit-pattern').val(),
              color: $('#edit-color').val(),
              background: $('#edit-background').val(),
              position: $('#pattern-position').val(),
              size: $('#edit-size').val(),
              height: $('#edit-height').val(),
              translateX: $('#edit-translate-x').val(),
              translateY: $('#edit-translate-y').val(),
            };

            setTimeout(function () {
              $('.sample__' + $('#edit-segment').val()).attr('class', 'sample__' + $('#edit-segment').val());
              new Drupal.patternCSSdemo(options);
            }, 10);

            event.preventDefault();
          }
        );

        // Pattern.css preview replay.
        $(once('pattern', '.pattern', context)).on(
          'change',
          function (event) {
            setTimeout(function () {
              $('.pattern__replay').trigger('click');
            }, 10);
          }
        );

        // Pattern.css preview replay.
        $(once('pattern__segment', '.segment', context)).on(
          'change',
          function (event) {
            let segment = $(this).val();
            $('.pattern__preview [class^="sample__"]').hide();
            if ($(this).val() == 'separator') {
              $("#height-value").html($('#edit-height').val());
            }
            setTimeout(function () {
              $('.sample__' + segment).show();
              $('.pattern__replay').trigger('click');
            }, 10);
          }
        ).trigger('change');

        // Pattern.css preview replay.
        $(once('pattern__position', '#background-position a', context)).on(
          'click',
          function (event) {
            $('#background-position a').removeClass('active');
            $("#pattern-position").val($(this).attr("title"));
            $(this).addClass('active');
            $('.pattern__replay').trigger('click');
          }
        );

        // Set default pattern position in edit form.
        if (once('pattern-position', '#pattern-position').length) {
          let currentPosition = $("#pattern-position").val();
          if (currentPosition !== '' && currentPosition !== 'center center') {
            $('#background-position a').removeClass('active');
            let targetPosition = '.' + currentPosition.replace(/\s+/g, '-').toLowerCase();
            $(targetPosition).addClass('active');
          }
        }

        // Pattern.css preview replay.
        $(once('pattern__size', '#edit-size', context)).on(
          'input',
          function (event) {
            $('.pattern__replay').trigger('click');
          }
        );

        // Pattern.css preview replay.
        $(once('pattern__height', '#edit-height', context)).on(
          'input',
          function (event) {
            $("#height-value").html($(this).val());
            $('.sample__separator').css({
              'height': $(this).val(),
            });
          }
        );

        // Pattern.css preview replay.
        $(once('pattern__translate_x', '#edit-translate-x', context)).on(
          'input',
          function (event) {
            $("#translate-x-value").html($(this).val());
            let translateY = parseInt($('.sample__image' + ' img').css('transform').split(',')[5]);
            if (typeof translateY === "undefined" || translateY === null || isNaN(translateY)) {
              translateY = 0;
            }
            $('.sample__image' + ' img').css({
              'transform': 'translate(' + $(this).val() + 'px, ' + translateY + 'px)',
            });
          }
        );

        // Pattern.css preview replay.
        $(once('pattern__translate_y', '#edit-translate-y', context)).on(
          'input',
          function (event) {
            $("#translate-y-value").html($(this).val());
            let translateX = parseInt($('.sample__image' + ' img').css('transform').split(',')[4]);
            if (typeof translateX === "undefined" || translateX === null || isNaN(translateX)) {
              translateX = 0;
            }
            $('.sample__image' + ' img').css({
              'transform': 'translate(' + translateX + 'px, ' + $(this).val() + 'px)',
            });
          }
        );

      }

      once('ginEditForm', '.region-content form', context).forEach(form => {
        const sticky = context.querySelector('.gin-sticky');
        const newParent = context.querySelector('.region-sticky__items__inner');

        if (sticky !== null && newParent && newParent.querySelectorAll('.gin-sticky').length === 0) {
          newParent.appendChild(sticky);

          // Attach form elements to main form
          const actionButtons = newParent.querySelectorAll('button, input, select, textarea');

          if (actionButtons.length > 0) {
            actionButtons.forEach((el) => {
              el.setAttribute('form', form.getAttribute('id'));
              el.setAttribute('id', el.getAttribute('id') + '--gin-edit-form');
            });
          }
        }
      });

      if ( once('pattern__color', '.coloris').length ) {
        Coloris({
          el: '.coloris',
          swatches: [
            '#264653',
            '#2a9d8f',
            '#e9c46a',
            '#f4a261',
            '#e76f51',
            '#d62828',
            '#023e8a',
            '#0077b6',
            '#0096c7',
            '#00b4d8',
            '#48cae4'
          ]
        });
      }

    }
  };

  Drupal.patternCSSdemo = function (options) {
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
