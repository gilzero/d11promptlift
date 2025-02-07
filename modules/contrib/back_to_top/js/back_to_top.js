/**
 * @file
 * Back To Top behaviors.
 */
(function ($, Drupal, once, drupalSettings) {

  var frame;
  var scrollTo = function (to, duration) {
    var element = document.scrollingElement || document.documentElement,
      start = element.scrollTop,
      change = to - start,
      startTs = performance.now(),
      easeOutQuart = function (t, b, c, d) {
	      t /= d;
	      t--;
	      return -c * (t * t * t * t - 1) + b;
      },
      animateScroll = function (ts) {
        var currentTime = ts - startTs;
        element.scrollTop = parseInt(easeOutQuart(currentTime, start, change, duration));
        if (currentTime < duration) {
          frame = requestAnimationFrame(animateScroll);
        } else {
          element.scrollTop = to;
        }
      };
    requestAnimationFrame(animateScroll);
  };

  Drupal.behaviors.backtotop = {
    attach: function (context, settings) {
      let isMobile = window.matchMedia("only screen and (max-width: 760px)").matches;
      if (!(settings.back_to_top.back_to_top_prevent_on_mobile && isMobile)) {
        var exist = $('#backtotop').length;
        if (exist == 0) {
          $(once('backtotop', 'body'), context).each(function () {
            $('body').append("<nav aria-label='" + Drupal.t("Back to top") + "'><button id='backtotop' aria-label='" + Drupal.t("Back to top") + "'>" + settings.back_to_top.back_to_top_button_text + "</button></nav>");
          });
        }
      }

      backToTop();
      $(document).on("scroll", function() {
        backToTop();
      });

      $(once('backtotop', '#backtotop'), context).each(function () {
        $(this).click(function () {
          $("html, body").bind("scroll mousedown DOMMouseScroll mousewheel keyup", function () {
            if (typeof frame !== 'undefined') {
              window.cancelAnimationFrame(frame);
            }
          });
          speed = settings.back_to_top.back_to_top_speed;
          speed = null || 1200;
          scrollTo(0, speed);
        });
      });

      /**
       * Hide show back to top links.
       */
      function backToTop() {
        if ($(window).scrollTop() > settings.back_to_top.back_to_top_button_trigger) {
          $('#backtotop').fadeIn();
        } else {
          $('#backtotop').fadeOut();
        }
      }
    }
  };
})(jQuery, Drupal, once, drupalSettings);
