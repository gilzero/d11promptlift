(function (Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.deepChatToggle = {
    attach: function (context, settings) {
      // Select all chat containers within the current context
      const chatContainers = context.querySelectorAll('.chat-container');

      chatContainers.forEach((container) => {
        // Check if the behavior has already been applied to this container
        if (container.dataset.deepChatToggleInitialized) {
          return; // Skip if already initialized
        }

        // Mark as initialized to prevent re-processing
        container.dataset.deepChatToggleInitialized = 'true';

        // Retrieve the unique chat ID
        const chatId = container.getAttribute('data-chat-id');
        if (!chatId) {
          console.warn('Chat container is missing a data-chat-id attribute.');
          return;
        }

        // Select the header element within the container
        const header = container.querySelector('.ai-deepchat--header');
        if (!header) {
          console.warn('Header with class .ai-deepchat--header not found in container:', container);
          return;
        }

        // Select the chat element within the container
        const chatElement = container.querySelector('.chat-element');
        if (!chatElement) {
          console.warn('Chat element with class .chat-element not found in container:', container);
          return;
        }

        // Optional: Select the toggle icon if present
        const toggleIcon = header.querySelector('.toggle-icon');

        // Function to update toggle icon (optional)
        const updateToggleIcon = (isOpen) => {
          if (toggleIcon) {
            toggleIcon.classList.toggle('is-opened', !isOpen);
            toggleIcon.classList.toggle('is-closed', isOpen);
          }
        };

        // Function to open the chat
        const openChat = () => {
          container.classList.add('chat-open');
          container.classList.remove('chat-collapsed');
          header.classList.add('active');
          header.setAttribute('aria-expanded', 'true');
          if (toggleIcon) updateToggleIcon(true);
          localStorage.setItem(`deepChatState_${chatId}`, 'open');
        };

        // Function to close the chat
        const closeChat = () => {
          container.classList.add('chat-collapsed');
          container.classList.remove('chat-open');
          header.classList.remove('active');
          header.setAttribute('aria-expanded', 'false');
          if (toggleIcon) updateToggleIcon(false);
          localStorage.setItem(`deepChatState_${chatId}`, 'collapsed');
        };

        // Toggle function
        const toggleChat = () => {
          if (container.classList.contains('chat-open')) {
            closeChat();
          } else {
            openChat();
          }
        };

        // Attach click event listener to the header
        header.addEventListener('click', toggleChat);

        // Attach keypress event listener for accessibility (e.g., Enter or Space keys)
        header.addEventListener('keypress', (event) => {
          if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            toggleChat();
          }
        });

        // Initialize state from localStorage
        const savedState = localStorage.getItem(`deepChatState_${chatId}`);
        if (savedState === 'open') {
          openChat();
        } else {
          closeChat();
        }
      });
    }
  };

})(Drupal, drupalSettings);
