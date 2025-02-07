((Drupal, once) => {
  Drupal.ui_suite_dsfr_notice = Drupal.ui_suite_dsfr_notice || {};

  /**
   * Close notice with an eventual memory.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behaviors for the keep open feature.
   */
  Drupal.behaviors.ui_suite_dsfr_notice = {
    attach(context) {
      once('ui_suite_dsfr_notice', '.fr-notice', context).forEach(
        (noticeContainer) => {
          const sessionClosedStateKey =
            noticeContainer.id &&
            noticeContainer.getAttribute('data-permanent-state') === 'true'
              ? `notice-${noticeContainer.id}.closed`
              : null;
          const closeButton = noticeContainer.querySelector('.fr-btn--close');
          if (
            sessionClosedStateKey &&
            sessionStorage.getItem(sessionClosedStateKey) === '1'
          ) {
            noticeContainer.remove();
          } else if (closeButton) {
            closeButton.addEventListener('click', () => {
              if (sessionClosedStateKey)
                sessionStorage.setItem(sessionClosedStateKey, '1');
              noticeContainer.remove();
            });
          }
        },
      );
    },
  };
})(Drupal, once);
