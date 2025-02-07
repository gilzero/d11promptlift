# UI Skins

This module allows:
- developers to define CSS variables from modules and themes
- site builders to set those CSS variables values on the theme settings
- developers to define themes from modules and themes
- site builders to set theme on the theme settings

**Example of CSS variables plugin declaration in the YML file**

```yaml
bs_blue:
  category: "Colors"
  type: "ui_skins_alpha_color"
  label: "Blue"
  default_values:
    ":root": "#0d6efdff"
```

You can disable a plugin by declaring a plugin with the same ID and if your
module has a higher weight than the module declaring the plugin, example:

```yaml
bs_blue:
  enabled: false
```

**Example of theme plugin declaration in the YML file**

```yaml
theme1:
  label: "Theme 1"
  description: "Theme"
  target: "body"  # Possible values: body, html. If not set body will be used.
  key: "data-theme" # Do not set to use class.
  value: "theme-blue" # Do not set to use plugin id. theme1 for this example.
  library: "my_theme/theme_mode" # Optional.
```


## Requirements

This module requires no modules outside of Drupal core.


## Installation

Install as you would normally install a contributed Drupal module. For further
information, see
[Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).


## Configuration

The module has no modifiable settings by itself.

Go to Appearance > CSS variables > _Theme name_ to configure CSS variables
overrides.

Go to Appearance > Settings > _Theme name_ to select a theme if available.
