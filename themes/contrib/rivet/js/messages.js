/**
 * @file
 * Message template overrides.
 */

((Drupal) => {
  /**
   * Overrides message theme function.
   *
   * @param {object} message
   *   The message object.
   * @param {string} message.text
   *   The message text.
   * @param {object} options
   *   The message context.
   * @param {string} options.type
   *   The message type.
   * @param {string} options.id
   *   ID of the message, for reference.
   *
   * @return {HTMLElement}
   *   A DOM Node.
   */
  Drupal.theme.message = ({ text }, { type, id }) => {
    const types = {
      default: 'info',
      info: 'info',
      message: 'info',
      status: 'success',
      success: 'success',
      warning: 'warning',
      error: 'danger',
    };

    const typeName = types[type] || 'info';
    const typeLabel = `${type}-alert-title`;
    const messagesTypes = Drupal.Message.getMessageTypeLabels();
    const messageWrapper = document.createElement('div');

    messageWrapper.setAttribute(
      'role',
      type === 'error' ? 'alert' : 'contentinfo',
    );
    messageWrapper.setAttribute(
      'class',
      `rvt-alert rvt-alert--${typeName} [ rvt-m-tb-md ]`,
    );
    messageWrapper.setAttribute('aria-labelledby', typeLabel);
    messageWrapper.setAttribute('data-rvt-alert', typeName);
    messageWrapper.setAttribute('data-drupal-message-id', id);
    messageWrapper.setAttribute('data-drupal-message-type', type);

    messageWrapper.innerHTML = `
      <h2 id="${typeLabel}" class="rvt-alert__title rvt-sr-only">
        ${messagesTypes[type]}
      </h2>
      <div class="rvt-alert__message">
        ${text}
      </div>
      <button class="rvt-alert__dismiss" data-rvt-alert-close>
        <span class="rvt-sr-only">Dismiss this alert</span>
        <svg fill="currentColor" width="16" height="16" viewBox="0 0 16 16">
          <path d="m3.5 2.086 4.5 4.5 4.5-4.5L13.914 3.5 9.414 8l4.5 4.5-1.414 1.414-4.5-4.5-4.5 4.5L2.086 12.5l4.5-4.5-4.5-4.5L3.5 2.086Z"></path>
        </svg>
      </button>
    `;

    return messageWrapper;
  };
})(Drupal);
