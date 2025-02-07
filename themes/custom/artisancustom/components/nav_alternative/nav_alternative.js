(function (Drupal, once) {
  'use strict';
  Drupal.behaviors.artisan_menu_collapse = {
    attach: function (context, settings) {
      once('artisan_menu_collapse', '.main-menu', context).forEach(function (element, index) {

        function makeMobileMenuCollapsible() {
          var mediaQuery = window.matchMedia("(max-width: 960px)");
          if (mediaQuery.matches) {
            let linksWithChildren = element.querySelectorAll('.nav-item--level-1.has-children');
            linksWithChildren.forEach(function (link, index) {
              let linkExpander = link.querySelector('.link-expand');
              let subMenuContainer = link.querySelector('.submenu-container');
              linkExpander.setAttribute('data-bs-toggle', 'collapse');
              linkExpander.setAttribute('data-bs-target', '.submenu-container--' + index);
              subMenuContainer.classList.add('submenu-container--' + index);
              subMenuContainer.classList.add('collapse');
            });
          }
        }
        makeMobileMenuCollapsible()
        window.addEventListener('resize', function(event){
          makeMobileMenuCollapsible();
        });
      });
    }
  };
})(Drupal, once);
