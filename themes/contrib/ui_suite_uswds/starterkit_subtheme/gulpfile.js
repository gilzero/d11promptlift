const uswds = require('@uswds/compile');

// Custom variables
const pkg = require('./package.json');
const { series, watch, src } = require('gulp');
const gulp = require('gulp');
const rename = require('gulp-rename');
const uglify = require('gulp-uglify-es').default;
const del = require('del');

/**
 * USWDS version
 */
uswds.settings.version = 3;

/**
 * Path settings
 * Set as many as you need
 */

// Where to output custom assets.
uswds.paths.dist.css = './assets/css';
uswds.paths.dist.fonts = './assets/uswds/fonts';
uswds.paths.dist.img = './assets/uswds/images';
uswds.paths.dist.js = './assets/uswds/js';

// Note this path is only used during init which is recommended to run once.
uswds.paths.src.projectSass = './assets/uswds/sass';

// // Custom scss files
uswds.paths.dist.theme = './sass/**/*';

/**
 * Fully delete the assets folder.
 */
function cleanUswds() {
  return del(pkg.paths.dist.uswds);
}

/**
 * Delete js folder.
 */
function cleanJs() {
  return del(pkg.paths.dist.js);
}

/**
 * Delete CSS folder.
 */
function cleanCss() {
  return del(pkg.paths.dist.css);
}

function buildJs() {
  return src(pkg.paths.js)
    .pipe(rename({ suffix: '.min' }))
    .pipe(uglify())
    .pipe(gulp.dest(pkg.paths.dist.js));
}

// USWDS build exports.
exports.init = uswds.init;
exports.updateUswds = uswds.updateUswds;

// Custom exports.
exports.copyUswds = series(cleanUswds, uswds.copyAssets);
exports.compileCss = series(cleanCss, uswds.compileSass);
exports.compileJs = series(cleanJs, buildJs);
exports.compileAll = series(this.compileJs, this.compileCss);

exports.watch = series(this.compileAll, () => {
  watch(pkg.paths.scss, series([this.compileSass]));
  watch(pkg.paths.js, series([this.compileJs]));
});

exports.default = series(this.copyUswds, this.compileAll);
