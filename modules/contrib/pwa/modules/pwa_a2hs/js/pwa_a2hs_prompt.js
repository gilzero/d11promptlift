(function ($, Drupal, once) {
  'use strict'

  Drupal.behaviors.pwa_a2hs_prompt = {
    attach: function(context, settings) {
      // Declare a global var for the deferred prompt.
      let deferredPrompt;

      // Create a button to be populated later on.
      let button = document.createElement("button");

      // Add classes that core and contrib themes use to style buttons for
      // consistency.
      $(button).addClass(['button', 'button--primary']);

      window.addEventListener('beforeinstallprompt', (e) => {
        // Prevent the mini info bar from showing.
        e.preventDefault();
        // Stash the event so it can be triggered later.
        deferredPrompt = e;

        // Add content to the button.
        var button_text = settings.pwaA2hs.pwaA2hsPrompt.button_text;
        button.innerHTML = button_text;

        // Use jQuery once() so the button doesn't get added multiple times.
        $(once('showButton', '.pwa-a2hs', context)).each(function () {
          $(this).removeClass('pwa-a2hs hidden').addClass('pwa-a2hs-active show').append(button);
        });
      });

      button.addEventListener('click', (e) => {
        // hide our user interface that shows our A2HS button
        // button.style.display = 'none';
        // Show the prompt
        deferredPrompt.prompt();
        // Wait for the user to respond to the prompt
        deferredPrompt.userChoice
          .then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
              console.log('User accepted the A2HS prompt');
            } else {
              console.log('User dismissed the A2HS prompt');
            }
            deferredPrompt = null;
          });
      });
    }
  };

})(jQuery, Drupal, once);