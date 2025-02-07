# Libraries provider

Libraries provider is a module meant to relieve themes and modules
from the loading of and configuration of third party assets.

## Features

* Allows to choose wether to load a library from a CDN or the local filesystem.
* Update the version of the library using the interface.
* When to load the minified or the normal version can be configured.
* Replacement libraries for other libraries can be configured.
* The user can choose what variant of the library to load in case there is options.
* When the library has custom options e.g sass variables, the different values can be configured.

The module relies on the
[hook_library_info_alter](https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!theme.api.php/function/hook_library_info_alter/8.8.x)
to apply transformations to the libraries based on what has been configured.

## Installation

Due to relying on external PHP libraries from packagist.org
to handle connections to the JsDelivr API
the module can only be installed using composer.

```
composer require drupal/libraries_provider:^1
```

Have look at
[Composer template for Drupal projects](https://github.com/drupal-composer/drupal-project)
to get familiar with managing Drupal projects with composer.

## Provide libraries

This module relies on adding additional data to the
`mytheme.libraries.yml` or `mymodule.libraries.yml`
so for example to load Fontawesome 5:

```yml
fontawesome:
  remote: https://github.com/FortAwesome/Font-Awesome
  version: 5.8.0
  license:
    name: Font Awesome Free License
    url: https://github.com/FortAwesome/Font-Awesome/blob/master/LICENSE.txt
    gpl-compatible: true
  css:
    base:
      https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.8.0/css/all.min.css:
        type: external
        minified: true
  libraries_provider:
    enabled: true
    source: cdn.jsdelivr.net
    npm_name: '@fortawesome/fontawesome-free'
```

The information under the `libraries_provider` key defines
the defaults for this library.

* `enabled`: When this library is attached it will only load its assets if enabled.

> This is useful for disabling optional libraries or loading replacements.

* `source`: The ID of the plugin that can handle this library by default.

> It needs to match the url information provided for the library css and js above.

* `npm_name`: The ID of this library on [NPM](https://www.npmjs.com/) to be able to retrieve information.

There is additional properties that can be defined:

* `blacklist_releases`(array of values): avoid certain versions to be listed.

> Sometimes the upstream build is not released correctly or has security problems.

* `minified`: `never`, `always` or `when_aggregating`(default)

> When the upstream release offers only minified versions or only normal versions the default needs to be changed.

* `variants_available`: list of possible variations of this library.
* `variant_regex`: the regex that matches the part of the path that needs to be replaced to change to the selected variant.
* `variant`: default variant.
* `replaces`: the ID of libraries that are replaced by this library.

> Some CSS skins already include the base library so loading the css twice is something to avoid.
> It can be useful when blundling or compiling libraries in a custom project and
> a they need to replace the libraries in other projects.
> The **libraries provider ID** is formed like NAMEOFMODULEORTHEME__KEYOFTHELIBRARY. Note the two underscores.
> The KEYOFTHELIBRARY can be obtained from the NAMEOFMODULEORTHEME.libraries.yml file.
> It similar to the concept of libraries-override but replacing all the assets provided by the library.

An example can be seen at:
https://gitlab.com/upstreamable/drulma/-/blob/8.x-1.x/drulma.libraries.yml

Note that a library being enabled does not mean that it will be loaded
on every page request. It still needs to be attached from
[a module](https://www.drupal.org/docs/8/creating-custom-modules/adding-stylesheets-css-and-javascript-js-to-a-drupal-8-module)
or [a theme](https://www.drupal.org/docs/8/theming/adding-stylesheets-css-and-javascript-js-to-a-drupal-8-theme)

## Source plugins

The assets can be attached to the frontend from a
CDN or from the local filesystem.

Currently the module implements a plugin for the JsDelivr CDN
and another for local libraries in the `/libraries` folder.

Other implementations for CDNs or ways to load a library can be added to
this module, please submit code changes so they can be added to this project.

## Loading local assets.

It is recommended to define libraries with the
[jsdelivr CDN](https://www.jsdelivr.com/)
so the user experience does not require additional
steps but there is
[many reasons not to use a CDN](https://www.sitepoint.com/7-reasons-not-to-use-a-cdn/)
so is advisable to load libraries from the local filesystem.

The local source plugin is preconfigured to search for the library
assuming the [asset packagist](https://asset-packagist.orocrm.com)
composer repository is used.
So for example to use Fontawesome 5 from this repository

https://asset-packagist.orocrm.com/package/detail?fullname=npm-asset/fortawesome--fontawesome-free

the local source plugin will search for it at `libraries/fortawesome--fontawesome-free`.

More details on how to add asset packagist repository to a project in the following PR:

https://github.com/drupal-composer/drupal-project/pull/286/files

Once the asset packagist repository is available and configured
to download to `/libraries` run a command like the following:

```
composer require npm-asset/fortawesome--fontawesome-free
```

And then the libraries provider UI will have the option of using the local version.

If the goal is to download a library and compile it locally to suit some specific needs,
for example using sass variables defined upstream to write your own sass code,
this module doesn't help much as the library will be already loaded from the local
filesystem, in some specific version and with the configuration chosen at
compile time e.g. minification, inlining SVGs, etc.

When the only need is to modify some sass/scss variables from the upstream library
the the "custom options" feature can be used (described below).

## Custom options

Given that the schema of what can be customized for a library is defined for the libraries provider module
and the php installation meets the requirements, custom options can be suppplied.
At the moment only sass variables are supported but other use cases can be contemplated.

The Drulma theme is an example of this feature.

https://git.drupalcode.org/project/drulma/tree/8.x-1.x/libraries_provider

To be able to compile sass/scss files into css the [sassphp](https://github.com/absalomedia/sassphp)
extension needs to be installed.

## Contributions

The project is open to improvements on how to load and discover
libraries but also feel free to open any discussion about
how to promote and stabilize it further so more modules and themes
can rely on it.

Patches on drupal.org are accepted but merge requests on
[gitlab](https://gitlab.com/upstreamable/drupal-libraries-provider) are preferred.

## Improvements

Since is now possible to retrieve information about the versions
of the libraries is a future feature might be to receive messages about
using outdated libraries.

## Real time communication

Join the [#libraries-provider](https://drupalchat.me/channel/libraries-provider)
channel on [drupalchat.me](https://drupalchat.me).

## Notes

This project started as a way to provide libraries for the
[Drulma theme](https://www.drupal.org/project/drulma)
so at the moment it covers that use case. This includes

* Loading the [Bulma](https://bulma.io/) CSS framework from CDN or local.
* Replace the the default css with a skin from [Bulmaswatch](https://jenil.github.io/bulmaswatch).
* Optionally load the Fontawesome 5 library with the [Libraries provider fontawesome](https://www.drupal.org/project/lp_fontawesome) module.
