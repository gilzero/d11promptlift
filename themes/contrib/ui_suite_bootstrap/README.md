# UI Suite Bootstrap

A site-builder friendly [Bootstrap](https://getbootstrap.com/) theme for
Drupal, using the [UI Suite](https://www.drupal.org/project/ui_suite) approach.

Use Bootstrap directly from Drupal backoffice (layout builder, manage display,
views, blocks...).

See the [docs](./docs) folder for more detailed documentation on:
- [details element](./docs/Details.md)
- [form API](./docs/Forms.md)
- [modal](./docs/Modal.md)
- [what is out of scope](./docs/Out-of-scope.md)


## Requirements

This theme requires the following modules:
- [Layout Options](https://www.drupal.org/project/layout_options)
- [UI Patterns](https://www.drupal.org/project/ui_patterns)
- [UI Styles](https://www.drupal.org/project/ui_styles)

This theme requires the Bootstrap library to be placed in the `libraries`
folder.

Optionally, this theme provides integration with
[Bootstrap icons](https://icons.getbootstrap.com), icons needs to be placed in
the `libraries` folder.


### Install libraries manually

You can download the Bootstrap library on its
[GitHub](https://github.com/twbs/bootstrap) page.

You can download the Bootstrap icons library on its
[GitHub](https://github.com/twbs/icons) page.


### Install libraries with Composer

#### With Asset Packagist

If you are using the website [Asset Packagist](https://asset-packagist.org), the
composer.json can be like:

```json
{
    "require": {
        "composer/installers": "2.*",
        "oomphinc/composer-installers-extender": "2.*",
        "npm-asset/bootstrap": "5.3.3",
        "npm-asset/bootstrap-icons": "1.11.3"
    },
    "repositories": {
        "asset-packagist": {
            "type": "composer",
            "url": "https://asset-packagist.org"
        }
    },
    "extra": {
        "installer-paths": {
            "app/libraries/{$name}": [
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

This version of Bootstrap will only contain compiled CSS/JS and SASS files.

#### With a package repository

You can declare a custom [package repositories](https://getcomposer.org/doc/05-repositories.md#package-2),
Example:

```json
{
    "require": {
        "asset/bootstrap": "5.3.3",
        "asset/bootstrap-icons": "1.11.3",
        "composer/installers": "2.*"
    },
    "repositories": {
        "asset-bootstrap": {
            "type": "package",
            "package": {
                "name": "asset/bootstrap",
                "version": "5.3.3",
                "type": "drupal-library",
                "extra": {
                    "installer-name": "bootstrap"
                },
                "dist": {
                    "type": "zip",
                    "url": "https://api.github.com/repos/twbs/bootstrap/zipball/6e1f75f420f68e1d52733b8e407fc7c3766c9dba",
                    "reference": "6e1f75f420f68e1d52733b8e407fc7c3766c9dba"
                }
            }
        },
        "asset-bootstrap-icons": {
            "type": "package",
            "package": {
                "name": "asset/bootstrap-icons",
                "version": "1.11.3",
                "type": "drupal-library",
                "extra": {
                    "installer-name": "bootstrap-icons"
                },
                "dist": {
                    "type": "zip",
                    "url": "https://api.github.com/repos/twbs/icons/zipball/8d88686c03c3768a2d82ba4f20c3c4e1b100fa29",
                    "reference": "8d88686c03c3768a2d82ba4f20c3c4e1b100fa29"
                }
            }
        }
    },
    "extra": {
        "installer-paths": {
            "app/libraries/{$name}": [
                "type:drupal-library"
            ]
        }
    }
}
```

This version will contain compiled CSS/JS and SASS files as well as all the
files used on for the development of Bootstrap.


## Installation

Install as you would normally install a contributed Drupal theme. For further
information, see
[Installing Drupal Themes](https://www.drupal.org/docs/extending-drupal/themes/installing-themes).


## Configuration

The theme has no menu or modifiable settings on its own.

Configuration is provided by the UI Suite ecosystem modules.


## Maintainers

Current maintainers:
- Florent Torregrosa - [Grimreaper](https://www.drupal.org/user/2388214)
- Pierre Dureau - [pdureau](https://www.drupal.org/user/1903334)
- Michael Fanini - [G4MBINI](https://www.drupal.org/user/2533498)

Supporting organizations:
- [Smile](https://www.drupal.org/smile)
