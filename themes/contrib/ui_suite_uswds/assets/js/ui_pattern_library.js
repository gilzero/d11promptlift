/**
 * @file
 * Attaches JS to UI Pattern Library page.
 */
(($) => {
  window.onload = () => {
    document.querySelectorAll('.usa-in-page-nav__link').forEach((link) => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));

        const accordion = target.closest('.usa-accordion');
        accordion.querySelector('.usa-accordion__button').setAttribute('aria-expanded', 'true');
        accordion.querySelector('.usa-accordion__content').removeAttribute('hidden');
      });
    });
  };
})(jQuery);
