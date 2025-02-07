# UI Suite DaisyUI

A site-builder friendly DaisyUI theme for Drupal, using the UI Suite approach.

Use DaisyUI directly from Drupal backoffice (layout builder, manage display,
views, blocks...).

## Requirements

This theme requires the following modules:

* UI Patterns 2
* UI Styles
* UI Skins
* UI Icons

This theme also requires the following libraries:

* Tailwind CSS
* DaisyUI
* Tailwind CSS Typography plugin
* Heroicons

This theme can be used with CDN hosted package of DaisyUI (version 4.12.14) or by building your own package.

If you want to build your own DaisyUI / Tailwind CSS package, please follow the "Tailwind CSS, DaisyUI and Tailwind CSS Typography plugin with NPM" section below.

This theme provides integration with Heroicons. Please follow the "Heroicons with Asset Packagist" section below.

## Required libraries

### Default: use CDN hosted package of DaisyUI

By default, the theme is configured to use CDN hosted package of DaisyUI (version 4.12.14) as follows: 
- inside `ui_suite_daisyui.libraries.yml` file:
```
daisyui_cdn:
  remote: https://daisyui.com/
  license:
    name: MIT
    url: https://github.com/saadeghi/daisyui/blob/master/LICENSE
    gpl-compatible: false
  css:
    theme:
      https://cdn.jsdelivr.net/npm/daisyui@4.12.14/dist/full.min.css:
        { minified: true }
  js:
    https://cdn.tailwindcss.com?plugins=typography: {}
```
- inside `ui_suite_daisyui.info.yml` file:
```
libraries:
  - ui_suite_daisyui/daisyui_cdn
```

### Manually: install Tailwind CSS, DaisyUI and Tailwind CSS Typography plugin with NPM

The following procedure requires NPM. To install NPM, please refer to the [official documentation](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm).

1. Inside `/web/libraries/daisyui` folder, run these commands to install all needed packages: 
- `npm install -D tailwindcss`
- `npm install -D daisyui@latest`
- `npm install -D @tailwindcss/typography`

2. Inside `/web/libraries/daisyui` folder, generate the `tailwind.config.js` file with this command: `npx tailwindcss init`.

3. Edit your `tailwind.config.js` file like this: 
```
/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    '../../themes/contrib/ui_suite_daisyui/dist/css/custom/*.css',
    '../../themes/contrib/ui_suite_daisyui/templates/*.{twig,js}',
    '../../themes/contrib/ui_suite_daisyui/templates/**/*.{twig,js}',
    '../../themes/contrib/ui_suite_daisyui/components/**/*.{twig,js,yml}',
    '../../themes/contrib/ui_suite_daisyui/*.ui_styles.yml',
    '../../themes/contrib/ui_suite_daisyui/ui_examples/*.yml',
  ],
  safelist: [
    'alert-info', 'alert-success', 'alert-warning', 'alert-error',
    'artboard-horizontal',
    'phone-1', 'phone-2', 'phone-3', 'phone-4', 'phone-5', 'phone-6',
    'badge-neutral', 'badge-primary', 'badge-secondary', 'badge-accent', 'badge-ghost', 'badge-info', 'badge-success', 'badge-warning', 'badge-error',
    'badge-xs', 'badge-sm', 'badge-md', 'badge-lg',
    'btn-neutral', 'btn-primary', 'btn-secondary', 'btn-accent', 'btn-ghost', 'btn-link', 'btn-info', 'btn-success', 'btn-warning', 'btn-error',
    'btn-xs', 'btn-sm', 'btn-md', 'btn-lg',
    'card-compact', 'card-side',
    'carousel-center', 'carousel-end',
    'chat-end',
    'chat-bubble-primary', 'chat-bubble-secondary', 'chat-bubble-accent', 'chat-bubble-info', 'chat-bubble-success', 'chat-bubble-warning', 'chat-bubble-error',
    'divider-horizontal',
    'divider-start', 'divider-end',
    'divider-neutral', 'divider-primary', 'divider-secondary', 'divider-accent', 'divider-success', 'divider-warning', 'divider-info', 'divider-error',
    'link-primary', 'link-secondary', 'link-accent', 'link-neutral', 'link-success', 'link-info', 'link-warning', 'link-error',
    'loading-spinner', 'loading-dots', 'loading-ring', 'loading-ball', 'loading-bars', 'loading-infinity',
    'loading-xs', 'loading-sm', 'loading-md', 'loading-lg',
    'menu-vertical', 'menu-horizontal',
    'menu-xs', 'menu-sm', 'menu-md', 'menu-lg',
    'progress-primary', 'progress-secondary', 'progress-accent', 'progress-info', 'progress-success', 'progress-warning', 'progress-error',
    'stats-vertical',
    'step-neutral', 'step-primary', 'step-secondary', 'step-accent', 'step-info', 'step-success', 'step-warning',  'step-error',
    'steps-vertical',
    'table-xs', 'table-sm', 'table-md', 'table-lg',
    'tabs-bordered', 'tabs-lifted', 'tabs-boxed',
    'tabs-xs', 'tabs-sm', 'tabs-lg',
    'timeline-vertical',
    'toast-start', 'toast-center', 'toast-end', 'toast-top', 'toast-middle', 'toast-bottom',
  ],
  theme: {
    extend: {}
  },
  plugins: [
    require("daisyui"),
    require('@tailwindcss/typography'),
  ],
  daisyui: {
    themes: [
      "light",
      "dark",
      "cupcake",
      "bumblebee",
      "emerald",
      "corporate",
      "synthwave",
      "retro",
      "cyberpunk",
      "valentine",
      "halloween",
      "garden",
      "forest",
      "aqua",
      "lofi",
      "pastel",
      "fantasy",
      "wireframe",
      "black",
      "luxury",
      "dracula",
      "cmyk",
      "autumn",
      "business",
      "acid",
      "lemonade",
      "night",
      "coffee",
      "winter",
      "dim",
      "nord",
      "sunset",
    ],
    darkTheme: "dark", // name of one of the included themes for dark mode
    base: true, // applies background color and foreground color for root element by default
    styled: true, // include daisyUI colors and design decisions for all components
    utils: true, // adds responsive and modifier utility classes
    prefix: "", // prefix for daisyUI classnames (components, modifiers and responsive class names. Not colors)
    logs: true, // Shows info about daisyUI version and used config in the console when building your CSS
    themeRoot: ":root", // The element that receives theme color CSS variables
  }
};
```

4. Inside `/web/libraries/daisyui` folder, create the `tailwind.css` file with this command: `touch tailwind.css`.

5. Edit your `tailwind.css` file like this: 
```
@tailwind base;
@tailwind components;
@tailwind utilities;
```

6. Eventually, execute the Tailwind CLI build process with this command: `npx tailwindcss -i tailwind.css -o daisyui.css --minify`

7. To root the theme to your custom build you should edit `ui_suite_daisyui.libraries.yml` and `ui_suite_daisyui.info.yml` files as follow:
- inside `ui_suite_daisyui.libraries.yml` file:
```
daisyui:
  css:
    theme:
      /libraries/daisyui/daisyui.css: { minified: true }
```
- inside `ui_suite_daisyui.info.yml` file:
```
libraries:
  - ui_suite_daisyui/daisyui
```

### Herocions with Asset Packagist

If you use the website Asset Packagist, the composer.json can be like:
```
{
    "require": {
        "composer/installers": "2.*",
        "oomphinc/composer-installers-extender": "2.*",
        "npm-asset/heroicons": "2.2.0"
    },
    "repositories": {
        "asset-packagist": {
            "type": "composer",
            "url": "https://asset-packagist.org"
        },
        "drupal": {
            "type": "composer",
            "url": "https://packages.drupal.org/8"
        }
    },
    "extra": {
        "installer-paths": {
            "web/libraries/{$name}": [
                "type:drupal-library",
                "type:bower-asset",
                "type:npm-asset"
            ]
        },
        "installer-types": [
            "bower-asset",
            "npm-asset"
        ]
    }
}
```

## Installation

Install as you would normally install a contributed Drupal theme. For further information, see [Installing Drupal Themes](https://www.drupal.org/docs/extending-drupal/themes/installing-themes).

## Configuration

The theme has no menu or modifiable settings on its own.

Configuration is provided by the UI Suite ecosystem modules.

## Maintainers

Current maintainer:
* Michael Fanini: [G4MBINI](https://www.drupal.org/u/g4mbini)


Supporting organization:
* Dropteam: [official website](https://www.dropteam.fr/) - [d.org company page](https://www.drupal.org/dropteam)