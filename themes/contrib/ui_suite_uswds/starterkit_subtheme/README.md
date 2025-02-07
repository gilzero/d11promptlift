# UI Suite USWDS subtheme theme

[UI Suite USWDS](https://www.drupal.org/project/ui_suite_uswds) subtheme.

## Setup.

1. Download node_modules `npm install`
2. Update gulp.js paths to needed values.
3. To compile, run from subtheme directory: `npm run build`

## Available NPM Commands

* `build` - gulp command to copy uswds assets and compile everything.
* `build:uswds:init` - gulp command to run uswds compile init command.
   Note should only be run once.
* `build:uswds:update` - gulp command to run uswds compile updateUswds command.
* `build:css` - gulp command to clean css folder and run uswds compileSass command.
* `build:js` - gulp command to clean js folder and run uswds compileJs command.
* `build:all` - gulp command to run both compileSass + compileSass commands.
* `watch` - gulp command to watch code and automatically run compile.
* `lint:css` - npm command to run stylelint on scss files.
* `lint:css:fix` - npm command to run stylelint fix on scss files.
* `lint:js` - npm command to run eslint on js files.

## Custom UI Styles

1. Create a [my-theme].ui_styles.yml file
2. Add values to that!
