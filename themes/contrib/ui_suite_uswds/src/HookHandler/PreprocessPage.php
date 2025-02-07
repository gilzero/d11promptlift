<?php

declare(strict_types=1);

namespace Drupal\ui_suite_uswds\HookHandler;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Theme\ThemeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Preprocess hook for details.
 */
class PreprocessPage implements ContainerInjectionInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected CurrentRouteMatch $currentRouteMatch;

  /**
   * The theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected ThemeManagerInterface $themeManager;

  /**
   * The menu link tree builder.
   */
  protected MenuLinkTreeInterface $menuLinkTree;

  /**
   * The entity type manager.
   */
  protected PluginManagerInterface $entityTypeManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   The current route match.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The theme manager.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menuLinkTree
   *   The menu link tree builder.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(CurrentRouteMatch $currentRouteMatch, ThemeManagerInterface $theme_manager, MenuLinkTreeInterface $menuLinkTree, PluginManagerInterface $entityTypeManager) {
    $this->currentRouteMatch = $currentRouteMatch;
    $this->themeManager = $theme_manager;
    $this->menuLinkTree = $menuLinkTree;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    // @phpstan-ignore-next-line
    return new static(
      $container->get('current_route_match'),
      $container->get('theme.manager'),
      $container->get('menu.link_tree'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Preprocess page.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocess(array &$variables): void {
    $route_match = $this->currentRouteMatch->getRouteName();
    if ($route_match === 'ui_patterns_library.overview') {
      $variables['#attached']['library'][] = 'ui_suite_uswds/ui_pattern_library_overrides';
    }

    // Provide the agency information for the footer.
    $footer_variables = [
      'footer_agency_name',
      'footer_agency_url',
      'footer_agency_logo_default',
      'footer_agency_logo',
      'contact_center',
      'phone',
      'email',
    ];
    foreach ($footer_variables as $footer_variable) {
      $variables[$footer_variable] = theme_get_setting('uswds_' . $footer_variable);
    }

    if (
      empty(theme_get_setting('uswds_facebook')) &&
      empty(theme_get_setting('uswds_instagram')) &&
      empty(theme_get_setting('uswds_linkedin')) &&
      empty(theme_get_setting('uswds_twitter')) &&
      empty(theme_get_setting('uswds_youtube')) &&
      empty(theme_get_setting('uswds_rss'))
    ) {
      $variables['footer_social_links'] = '';
    }
    else {
      $footer_social_links = [];

      if (!empty(theme_get_setting('uswds_facebook'))) {
        $footer_social_links[] = [
          'title' => 'Facebook',
          'url' => theme_get_setting('uswds_facebook'),
          'icon' => $variables['uswds_images'] . 'usa-icons/facebook.svg',
        ];
      }

      if (!empty(theme_get_setting('uswds_instagram'))) {
        $footer_social_links[] = [
          'title' => 'Instagram',
          'url' => theme_get_setting('uswds_instagram'),
          'icon' => $variables['uswds_images'] . 'usa-icons/instagram.svg',
        ];
      }

      if (!empty(theme_get_setting('uswds_linkedin'))) {
        $footer_social_links[] = [
          'title' => 'LinkedIn',
          'url' => theme_get_setting('uswds_linkedin'),
          'icon' => $variables['uswds_images'] . 'usa-icons/linkedin.svg',
        ];
      }

      if (!empty(theme_get_setting('uswds_twitter'))) {
        $footer_social_links[] = [
          'title' => 'Twitter',
          'url' => theme_get_setting('uswds_twitter'),
          'icon' => $variables['uswds_images'] . 'usa-icons/twitter.svg',
        ];
      }

      if (!empty(theme_get_setting('uswds_youtube'))) {
        $footer_social_links[] = [
          'title' => 'YouTube',
          'url' => theme_get_setting('uswds_youtube'),
          'icon' => $variables['uswds_images'] . 'usa-icons/youtube.svg',
        ];
      }

      if (!empty(theme_get_setting('uswds_rss'))) {
        $footer_social_links[] = [
          'title' => 'rss',
          'url' => theme_get_setting('uswds_rss'),
          'icon' => $variables['uswds_images'] . 'usa-icons/rss_feed.svg',
        ];
      }

      $variables['footer_social_links'] = $footer_social_links;
    }

    // Show the official "back to top" link in footer.
    if (theme_get_setting('uswds_back_to_top')) {
      $variables['back_to_top'] = theme_get_setting('uswds_back_to_top');
      $variables['back_to_top_text'] = theme_get_setting('uswds_back_top_top_text');
    }

    // Fix the footer logo path.
    if ($variables['footer_agency_logo_default']) {
      $variables['footer_agency_logo'] = theme_get_setting('logo.url');
    }
    elseif ($variables['footer_agency_logo']) {
      $variables['footer_agency_logo'] = base_path() . $this->themeTrailFile($variables['footer_agency_logo']);
    }

    $variables['header_style'] = theme_get_setting('uswds_header_style');
    $variables['footer_style'] = theme_get_setting('uswds_footer_style');
    $variables['use_megamenu'] = theme_get_setting('uswds_header_mega');

    // Show the official U.S. Government banner?
    if (theme_get_setting('uswds_government_banner')) {
      $variables['government_banner'] = [
        '#theme' => 'government_banner',
        '#image_base' => $variables['active_theme_path'] . '/package/dist/img',
      ];
    }
    // Provide this variable so that additional classes can be added to the main
    // section, similar to the header and footer.
    $variables['main_classes'] = '';
    // Add raw menus links.
    if (theme_get_setting('uswds_primary_menu') !== NULL) {
      $variables = $this->addRawMenu($variables, "primary_menu", theme_get_setting('uswds_primary_menu'));
    }
    if (theme_get_setting('uswds_secondary_menu') !== NULL) {
      $variables = $this->addRawMenu($variables, "secondary_menu", theme_get_setting('uswds_secondary_menu'));
    }
    if (theme_get_setting('uswds_footer_menu') !== NULL) {
      $variables = $this->addRawMenu($variables, "footer_menu", theme_get_setting('uswds_footer_menu'));
    }

    $variables['search_url_action'] = theme_get_setting('search_url_action');
    $variables['search_name'] = theme_get_setting('search_name');

    if (theme_get_setting('sign_up_block_display')) {
      $variables['sign_up_block_url'] = theme_get_setting('sign_up_block_url');
      $variables['sign_up_block_header'] = theme_get_setting('sign_up_block_header');
      $variables['sign_up_block_label'] = theme_get_setting('sign_up_block_label');
      $variables['sign_up_block_button_label'] = theme_get_setting('sign_up_block_button_label');
    }
  }

  /**
   * Add single menu links in #menus.
   *
   * @return array
   *   The altered renderable variables.
   */
  protected function addRawMenu(array $variables, string $variable, string $menu_id): array {
    $parameters = new MenuTreeParameters();
    // Empty parameters because not configurable.
    $tree = $this->menuLinkTree->load($menu_id, $parameters);
    $manipulators = [
        ['callable' => 'menu.default_tree_manipulators:checkAccess'],
        ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $this->menuLinkTree->transform($tree, $manipulators);
    $tree = $this->menuLinkTree->build($tree);
    if (!isset($tree["#items"])) {
      return $variables;
    }
    if (isset($tree["#cache"])) {
      $variables["#cache"] = array_merge($variables["#cache"] ?? [], $tree["#cache"]);
    }
    $variables[$variable] = $tree["#items"];
    return $variables;
  }

  /**
   * Gets the full theme trail, from active to top-level base.
   *
   * @return array
   *   An array of all themes in the trail, ordered from active to
   *   top-level base.
   */
  protected function themeTrail(): array {
    $theme = $this->themeManager->getActiveTheme();
    $all = [$theme->getName() => $theme] + $theme->getBaseThemeExtensions();
    // This gives us a complete lineage of themes, but we actually only
    // care about the USWDS Base theme and any child themes.
    $trail = [];
    foreach ($all as $key => $value) {
      $trail[$key] = $value;
      if ('ui_suite_uswds' == $key) {
        break;
      }
    }
    return $trail;
  }

  /**
   * Finds the first occurrence of a given file in the theme trail.
   *
   * @param string $file
   *   The relative path to a file.
   *
   * @return string
   *   The path to the file. If the file does not exist at all, it will simply
   *   return the path of the file as it would be if it existed in the given
   *   theme directly. This ensures that the code that uses this function does
   *   not break if a file does not exist anywhere.
   */
  protected function themeTrailFile(string $file): string {
    $trail = $this->themeTrail();
    foreach ($trail as $theme) {
      $current = $theme->getPath() . '/' . $file;
      if (file_exists($current)) {
        return $current;
      }
    }
    // The default (fallback) path is the path of the active theme, even if it
    // does not actually have that file.
    $active = array_shift($trail);
    return $active->getPath() . '/' . $file;
  }

}
