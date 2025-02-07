<?php

namespace Drupal\ui_suite_uswds;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * UI Suite USWDS theme settings manager.
 */
class SettingsManager implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * Constructs a SettingsManager object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(protected ModuleHandlerInterface $moduleHandler, protected EntityTypeManagerInterface $entityTypeManager) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    // @phpstan-ignore-next-line
    return new static(
      $container->get('module_handler'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Return menu Settings.
   */
  protected function menuSettingsForm($title, $value): array {
    try {
      $all_menus = $this->entityTypeManager->getStorage('menu')->loadMultiple();
      $menus = [
        "" => "(None)",
      ];
      foreach ($all_menus as $id => $menu) {
        $menus[$id] = $menu->label();
      }
      asort($menus);
      return [
        '#type' => 'select',
        '#title' => $title,
        '#options' => $menus,
        '#default_value' => $value,
      ];
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException) {
    }
    return [];
  }

  /**
   * Alters theme settings form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param string|null $form_id
   *   The form id.
   *
   * @see hook_form_alter()
   */
  public function themeSettingsAlter(array &$form, FormStateInterface $form_state, ?string $form_id): void {
    // CDN Provider.
    $form['cdn_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('CDN Settings'),
      '#open' => FALSE,
      'menu-help' => [
        '#markup' => $this->t('NOTE: The "CDN Provider" options below is an option for loading USWDS CSS and JS. Using a "CDN Provider" for loading USWDS externally, like jsDelivr, should not be considered a good option for a production website. Please, follow the instructions and download your USWDS files.'),
      ],
      'cdn_provider' => [
        '#type' => 'select',
        '#title' => $this->t('CDN Provider'),
        '#description' => $this->t('Choose between a custom CDN source or none. None will expect USWDS through /package/dist folder or sass.'),
        '#options' => [
          'none' => $this->t('- None -'),
          'custom' => $this->t('Custom'),
        ],
        '#default_value' => theme_get_setting('cdn_provider'),
      ],
      'cdn_custom_img' => [
        '#type' => 'textfield',
        '#title' => $this->t('USWDS Image URL'),
        '#default_value' => theme_get_setting('cdn_custom_img'),
        '#states' => [
          'visible' => [
            ':input[name="cdn_provider"]' => ['value' => 'custom'],
          ],
        ],
      ],
      'cdn_custom_css' => [
        '#type' => 'textfield',
        '#title' => $this->t('USWDS CSS URL'),
        '#description' => $this->t('It is best to use https protocols here as it will allow more flexibility if the need ever arises.'),
        '#default_value' => theme_get_setting('cdn_custom_css'),
        '#states' => [
          'visible' => [
            ':input[name="cdn_provider"]' => ['value' => 'custom'],
          ],
        ],
      ],
      'cdn_custom_js' => [
        '#type' => 'textfield',
        '#title' => $this->t('USWDS JavaScript URL'),
        '#description' => $this->t('It is best to use https protocols here as it will allow more flexibility if the need ever arises.'),
        '#default_value' => theme_get_setting('cdn_custom_js'),
        '#states' => [
          'visible' => [
            ':input[name="cdn_provider"]' => ['value' => 'custom'],
          ],
        ],
      ],
    ];

    $form['search_fieldset'] = [
      '#type' => 'details',
      '#title' => $this->t('Search settings'),
      '#open' => FALSE,
      'search_url_action' => [
        '#type' => 'textfield',
        '#title' => $this->t('Search URL'),
        '#description' => $this->t('Used as the action in the search form. Must be valid URL or start with /'),
        '#required' => FALSE,
        '#default_value' => theme_get_setting('search_url_action'),
      ],
      'search_name' => [
        '#type' => 'textfield',
        '#title' => $this->t('Search keyword name'),
        '#description' => $this->t('Used as the name in the search form. Used for keyword searching.'),
        '#required' => FALSE,
        '#default_value' => theme_get_setting('search_name'),
      ],
    ];

    // Header style.
    $form['header_style_fieldset'] = [
      '#type' => 'details',
      '#title' => $this->t('Header settings'),
      '#open' => FALSE,
      'uswds_header_style' => [
        '#type' => 'select',
        '#required' => TRUE,
        '#title' => $this->t('Choose a style of header to use'),
        '#options' => [
          'basic' => $this->t('Basic'),
          'extended' => $this->t('Extended (Default)'),
        ],
        '#default_value' => theme_get_setting('uswds_header_style') ?? 'extended',
      ],
      'uswds_primary_menu' => $this->menuSettingsForm(
        $this->t("Primary menu"),
        theme_get_setting('uswds_primary_menu') ?? "main"
      ),
      'uswds_secondary_menu' => $this->menuSettingsForm(
        $this->t("Secondary menu"),
        theme_get_setting('uswds_secondary_menu') ?? "account"
      ),
      'uswds_header_mega' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Use megamenu in the header?'),
        '#description' => $this->t('Site building note: Megamenu require hierarchical three-level menus, where the second level of menu items are used to determine the "columns" for the megamenu.'),
        '#default_value' => theme_get_setting('uswds_header_mega'),
      ],
      'uswds_government_banner' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Display the official U.S. government banner at the top of each page.'),
        '#default_value' => theme_get_setting('uswds_government_banner'),
      ],
    ];

    if (!$this->moduleHandler->moduleExists('search')) {
      $form['header_style_fieldset']['uswds_search']['#description'] = $this->t('Requires the core Search module to be enabled.');
    }

    // Footer style.
    $form['footer_style_fieldset'] = [
      '#type' => 'details',
      '#title' => $this->t('Footer settings'),
      '#open' => FALSE,
      'uswds_back_to_top' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Display the "back to top" link in the footer.'),
        '#default_value' => theme_get_setting('uswds_back_to_top'),
      ],
      'uswds_back_top_top_text' => [
        '#type' => 'textfield',
        '#title' => $this->t('Back to top text'),
        '#default_value' => theme_get_setting('uswds_back_top_top_text'),
        '#states' => [
          'visible' => [
            'input[name="uswds_back_to_top"]' => ['checked' => TRUE],
          ],
        ],
      ],
      'uswds_footer_style' => [
        '#type' => 'select',
        '#required' => TRUE,
        '#title' => $this->t('Choose a style of footer to use'),
        '#description' => $this->t('See <a href="https://designsystem.digital.gov/components/footer/">USWDS Footer </a> for more details'),
        '#options' => [
          'big' => $this->t('Big (default)'),
          'medium' => $this->t('Medium'),
          'slim' => $this->t('Slim'),
        ],
        '#default_value' => theme_get_setting('uswds_footer_style') ?? 'big',
      ],
      'uswds_footer_style_help' => [
        '#type' => 'markup',
        '#markup' => '<span>Using "Big" option will make first menu items appear as headers</span><br><span>Using "Medium" or "Slim" options will only display first level of menu.</span>',
      ],
      'uswds_footer_menu' => $this->menuSettingsForm(
        $this->t("Footer menu"),
        theme_get_setting('uswds_footer_menu') ?? "main"
      ),
      'uswds_footer_agency_name' => [
        '#type' => 'textfield',
        '#title' => $this->t('Footer agency name'),
        '#default_value' => theme_get_setting('uswds_footer_agency_name'),
        '#states' => [
          'visible' => [
            'select[name="uswds_footer_style"]' => ['value' => 'big'],
          ],
        ],
      ],
      'uswds_footer_agency_url' => [
        '#type' => 'textfield',
        '#title' => $this->t('Footer agency URL'),
        '#default_value' => theme_get_setting('uswds_footer_agency_url'),
      ],
      'uswds_footer_agency_logo_default' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Use general logo image'),
        '#description' => $this->t('See Logo image section for details'),
        '#default_value' => theme_get_setting('uswds_footer_agency_logo_default'),
      ],
      'uswds_footer_agency_logo' => [
        '#type' => 'textfield',
        '#title' => $this->t("Path to footer agency logo (from this theme's folder)"),
        '#description' => $this->t('For example: images/footer-agency.png'),
        '#default_value' => theme_get_setting('uswds_footer_agency_logo'),
        '#states' => [
          'visible' => [
            'input[name="uswds_footer_agency_logo_default"]' => ['checked' => FALSE],
          ],
        ],
      ],
      'uswds_contact_center' => [
        '#type' => 'textfield',
        '#title' => $this->t('Name of contact center'),
        '#default_value' => theme_get_setting('uswds_contact_center'),
      ],
      'uswds_email' => [
        '#type' => 'textfield',
        '#title' => $this->t('Email'),
        '#default_value' => theme_get_setting('uswds_email'),
        '#states' => [
          'visible' => [
            'select[name="uswds_footer_style"]' => ['value' => 'big'],
          ],
        ],
      ],
      'uswds_phone' => [
        '#type' => 'textfield',
        '#title' => $this->t('Phone'),
        '#default_value' => theme_get_setting('uswds_phone'),
        '#states' => [
          'visible' => [
            'select[name="uswds_footer_style"]' => ['value' => 'big'],
          ],
        ],
      ],
      'uswds_social_links_group' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Social media links'),
        '#states' => [
          'visible' => [
            'select[name="uswds_footer_style"]' => ['!value' => 'slim'],
          ],
        ],
        'uswds_facebook' => [
          '#type' => 'textfield',
          '#title' => $this->t('Facebook link'),
          '#default_value' => theme_get_setting('uswds_facebook'),
          '#states' => [
            'visible' => [
              'select[name="uswds_footer_style"]' => ['!value' => 'slim'],
            ],
          ],
        ],
        'uswds_instagram' => [
          '#type' => 'textfield',
          '#title' => $this->t('Instagram'),
          '#default_value' => theme_get_setting('uswds_instagram'),
          '#states' => [
            'visible' => [
              'select[name="uswds_footer_style"]' => ['!value' => 'slim'],
            ],
          ],
        ],
        'uswds_linkedin' => [
          '#type' => 'textfield',
          '#title' => $this->t('Linkedin'),
          '#default_value' => theme_get_setting('uswds_linkedin'),
          '#states' => [
            'visible' => [
              'select[name="uswds_footer_style"]' => ['!value' => 'slim'],
            ],
          ],
        ],
        'uswds_twitter' => [
          '#type' => 'textfield',
          '#title' => $this->t('Twitter link'),
          '#default_value' => theme_get_setting('uswds_twitter'),
          '#states' => [
            'visible' => [
              'select[name="uswds_footer_style"]' => ['!value' => 'slim'],
            ],
          ],
        ],
        'uswds_youtube' => [
          '#type' => 'textfield',
          '#title' => $this->t('Youtube link'),
          '#default_value' => theme_get_setting('uswds_youtube'),
          '#states' => [
            'visible' => [
              'select[name="uswds_footer_style"]' => ['!value' => 'slim'],
            ],
          ],
        ],
        'uswds_rss' => [
          '#type' => 'textfield',
          '#title' => $this->t('RSS feed'),
          '#default_value' => theme_get_setting('uswds_rss'),
          '#states' => [
            'visible' => [
              'select[name="uswds_footer_style"]' => ['!value' => 'slim'],
            ],
          ],
        ],
      ],
    ];

    $form['sign_up_block_fieldset'] = [
      '#type' => 'details',
      '#title' => $this->t('Sign up block settings'),
      '#open' => FALSE,
      'sign_up_block_display' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Display Sign up block in footer'),
        '#default_value' => theme_get_setting('sign_up_block_display'),
      ],
      'sign_up_block_url' => [
        '#type' => 'textfield',
        '#required' => FALSE,
        '#title' => $this->t('Sign up block URL'),
        '#description' => $this->t('Used for sign up block action value.'),
        '#default_value' => theme_get_setting('sign_up_block_url'),
        '#states' => [
          'visible' => [
            'input[name="sign_up_block_display"]' => ['checked' => TRUE],
          ],
        ],
      ],
      'sign_up_block_header' => [
        '#type' => 'textfield',
        '#required' => FALSE,
        '#title' => $this->t('Sign up block Header'),
        '#description' => $this->t('Used as sign up block header. Example "Sign up"'),
        '#default_value' => theme_get_setting('sign_up_block_header'),
        '#states' => [
          'visible' => [
            'input[name="sign_up_block_display"]' => ['checked' => TRUE],
          ],
        ],
      ],
      'sign_up_block_label' => [
        '#type' => 'textfield',
        '#required' => FALSE,
        '#title' => $this->t('Sign up block label'),
        '#description' => $this->t('Used as sign up block label. Example "Your email address"'),
        '#default_value' => theme_get_setting('sign_up_block_label'),
        '#states' => [
          'visible' => [
            'input[name="sign_up_block_display"]' => ['checked' => TRUE],
          ],
        ],
      ],
      'sign_up_block_button_label' => [
        '#type' => 'textfield',
        '#required' => FALSE,
        '#title' => $this->t('Sign up block button label'),
        '#description' => $this->t('Used as sign up block button label. Example "Sign up"'),
        '#default_value' => theme_get_setting('sign_up_block_label'),
        '#states' => [
          'visible' => [
            'input[name="sign_up_block_display"]' => ['checked' => TRUE],
          ],
        ],
      ],
    ];

    // Menu settings.
    $saved_bypass = theme_get_setting('uswds_menu_bypass');
    if (empty($saved_bypass)) {
      $saved_bypass = [];
    }

    $form['menu_fieldset'] = [
      '#type' => 'details',
      '#title' => $this->t('Menu settings'),
      '#open' => FALSE,
      'uswds_menu_bypass' => [
        '#type' => 'checkboxes',
        '#title' => $this->t('Bypass USWDS menu processing for these menus.'),
        '#description' => $this->t('Choose the menus which you would like to exempt from USWDS alterations. Note that a cache clear may be necessary after changing these settings.'),
        '#options' => [
          'primary_menu' => $this->t('Primary menu'),
          'secondary_menu' => $this->t('Secondary menu'),
          'sidebar_first' => $this->t('Sidebar First menu'),
          'sidebar_second' => $this->t('Sidebar Second menu'),
          'footer_primary' => $this->t('Footer primary'),
        ],
        '#default_value' => $saved_bypass,
      ],
    ];

    $form['subtheme'] = [
      '#type' => 'details',
      '#title' => $this->t('Subtheme'),
      '#description' => $this->t("Create subtheme."),
      '#open' => FALSE,
    ];

    $form['subtheme']['subtheme_folder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subtheme location'),
      '#default_value' => 'themes/custom',
      '#description' => $this->t("Relative path to the webroot <em>%root</em>. No trailing slash.", [
        '%root' => DRUPAL_ROOT,
      ]),
    ];

    $form['subtheme']['subtheme_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subtheme name'),
      '#default_value' => 'UI Suite USWDS subtheme',
      '#description' => $this->t("If name is empty, machine name will be used."),
    ];

    $form['subtheme']['subtheme_machine_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subtheme machine name'),
      '#default_value' => 'ui_suite_uswds_subtheme',
      '#description' => $this->t("Use only lower-case letters, numbers, and underscores. Name must start with a letter. For details, see <a href='https://www.drupal.org/docs/creating-custom-modules/naming-and-placing-your-drupal-module'>Naming and placing your Drupal module</a>."),
    ];

    $form['subtheme']['create'] = [
      '#type' => 'submit',
      '#name' => 'subtheme_create',
      '#value' => $this->t('Create'),
      '#button_type' => 'danger',
      '#attributes' => [
        'class' => ['btn btn-danger'],
      ],
      '#submit' => ['ui_suite_uswds_form_system_theme_settings_subtheme_submit'],
      '#validate' => ['ui_suite_uswds_form_system_theme_settings_subtheme_validate'],
    ];
  }

}
