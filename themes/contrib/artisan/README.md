Artisan arises from the need to have a base theme that allows most of its components to be reused without having to completely overwrite them in the custom theme of a specific project.
Its objective is to offer a functional, fast and consolidated design base that is easily extensible.

From interface, we can define aspects such as the main color palette, page width and font sizes for different elements.
These settings become CSS variables applied to specific elements, allowing you to have a functional base design quickly and without the need to compile CSS or empty the cache.

## Installation & Setup

### npm and node minimal versions
- npm >=6.14
- node >=12.14

1. Install the theme as you would normally install a contributed Drupal module or theme via Composer.
```twig
 composer require drupal/artisan
```
2. Enable Artisan and set it as **default** on /admin/appearance.
3. Copy the web/themes/contrib/artisan/artisan_starterkit folder to your custom theme folder.
4. Rename the artisan_starterkit copied folder & files to your custom theme name.
5. Go to your custom theme folder and run the following command to install the dependencies.
```twig
npm install
```
6. Run of the
```twig
npm run dev
```
### For development environment
```twig
npm run development
```
```twig
npm run watch
```
### For production environment
```twig
npm run production
```

### PHP script to generate a new theme
```php
cd web #DRUPALROOT
php core/scripts/drupal generate-theme artisan_subtheme --name "Artisan Subtheme" --starterkit "artisan_starterkit" --path themes/custom
```

### Drush command to generate a new theme (generation, compiling, install & set default)
```twig
drush --include="web/themes/contrib/artisan" artisan
```

*Optional "name" param can be passed, used with "-y" it will make everything without asking anything.
```twig
drush --include="web/themes/contrib/artisan" artisan "Artisan Mysite" -y
```

### Artisan Styleguide - Optional ecosystem module
You can also optionally install complementary module https://www.drupal.org/project/artisan_styleguide to help you visualize, iterate & refine your new theme SDCs by "/artisan-styleguide" guidelines.

## Customization options
- Go to /admin/appearance/settings/{your_custom_theme} under Appearance --> Configuration --> {Your theme name}.
- Try an already preconfigured design or create your own from zero!

## Developer notes

### CSS Vars
Each component should define their own CSS Variables inside their own selector where their values should be a set of fallbacks.
```css
--component-property: var(--THEME-COMPONENT-PROP, --BOOTSTRAP-PRO-VARIABLE, 1rem)
--card-border: var(--theme--card-border, --bs-card-border, 1rem)
```
You can find you complete CSS VARs suggestion under **"Artisan - Customizations CSS variables preview"** in your theme config.


### SDC Usage
- Global **attributes** property at the first DOM level, then specific attributes if needed.
- SDC embeds or includes should be done with the 'only' option, in order to avoid SDC unexpected/unnecessary variables scope.

Example:
```twig
{% include 'sdc_examples:my-button' with { text: 'Click Me', iconType: 'external' } only %}
{% embed 'sdc_examples:my-button' with { text: 'Click Me', iconType: 'external' } only %} {% endembed %}
```

- Artisan SDCs with css/js dependency: Declared in Artisan and the sub theme should use this SDC and declare JS/CSS dependency.
- Artisan SDCs without css/js dependency: Declared in Artisan.
- Artisan SDC should be customizable enough to avoid having to override completely, in most use cases, by attributes (genereral & specific) props, slots by twig blocks to customize its content & additional elements by bem class or additional specific attributes but with extra preset of minimal needed classes (by .addClass).
- Artisan SDCs with bootstrap css/js dependency: bootstrap css/js should be declared in subtheme as a library & injected as dependecy into any sdc that requires it (as dropdown), in order to avoid reduntand css/js with multiple usages. Also to avoid injecting it always into main theme styles/js.

Example SDC with mandatory attributes
```twig
<div attributes.addClass(['swiper'])>
  <div swiper_wrapper_attributes.addClass(['swiper-wrapper'])>
    <div swiper_slides_attributes.addClass(['swiper-slide'])>Slide 1</div>
  </div>
</div
```

Usage of this SDC, adding additional classes
```twig
{% embed 'artisan:swiper' with {
  attributes: create_attribute({'class': ['example-class']}),
  swiper_wrapper_attributes: create_attribute({'class': ['example-class-1', 'example-class-2']}),
  swiper_slides_attributes: create_attribute({'class': ['container', 'py-5']}),
} only %}
{% endembed %}
```

### Extending Artisan

#### Adding a new component customization
```php
function artisan_starterkit_artisan_customizations_alter(&$customizations) {
  $customizations['example'] = [
    'wrapper' => 'component',
    'label' => t('Example'),
    'description' => t('My cool example component'),
    'type_default' => 'textfield',
    'selector_default' => 'div[data-component-id="artisan_starterkit:example"]',
    'list' => [
      'font_size' => [
        'label' => t('Font size'),
        'description' => ArtisanCustomizations::FONT_SIZE_EXAMPLE,
      ],
      'color' => [
        'label' => t('Color'),
        'type' => 'color',
      ],
    ],
  ];
}
```
By doing this, the new customization options will appear under your subtheme settings
- Component Example Font Size
- Component Example Color

Which will also generate the following CSS Vars:
```css
--theme-example-font-size
--theme-example-color
```

To use it in your component style

```css
div[data-component-id="artisan_starterkit:example"] {
  --example-font-size: var(--theme-example-font-size, var(--FALLBACK-CSS-VAR, FALLBACK-CSS-VALUE));
  --example-color: var(--theme-example-color, var(--FALLBACK-CSS-VAR, FALLBACK-CSS-VALUE));
  font-size: var(--example-size);
  color: var(--example-color);
}
```


#### Removing buttons variants from default buttons customization
```php
function artisan_starterkit_artisan_customizations_alter(&$customizations) {
  // Use just primary & secondary with outline & link, discard others.
  // @see ArtisanCustomizations::getDefinitions().
  // @see ArtisanCustomizationsBtnVariantsTrait::getBtnVariantsList().
  $buttons_to_remove = [
    'btn_success',
    'btn_outline_success',
    'btn_danger',
    'btn_outline_danger',
    'btn_warning',
    'btn_outline_warning',
    'btn_info',
    'btn_outline_info',
    'btn_light',
    'btn_outline_light',
    'btn_dark',
    'btn_outline_dark',
  ];
  foreach ($buttons_to_remove as $delta) {
    if (!empty($customizations[$delta])) {
      unset($customizations[$delta]);
    }
  }
}
```

## Helpful

### Sitcky / fixed top auto calculated
In order to place an element like a search sidebar positioned sticky or fixed
you will be able to use "sticky-fixed-offset-top" css class declared in
"_helpers.scss" o "--theme-sticky-fixed-offset-top" CSS variable.
So top position is auto calculated & refreshed taking into account possible
sticky header & admin menu toolbar.

## Authors
- [Cristian Aliaga](https://www.drupal.org/u/crzdev)
- [Alejandro Cabarcos](https://www.drupal.org/u/alejandro-cabarcos)
- [Fran Rouco](https://www.drupal.org/u/frouco)
- [Alberto Antoranz](https://www.drupal.org/u/alzz)

## Verbose mode
To see the head style tag CSS variables generated via your customizations in verbose mode, set the following line in settings.php or settings.local.php (Not recommended in production environments)
```php
$settings['artisan_customizations_verbose'] = TRUE;
```
