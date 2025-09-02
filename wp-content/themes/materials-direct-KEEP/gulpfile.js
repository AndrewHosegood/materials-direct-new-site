const gulp = require("gulp");
const sass = require("gulp-sass")(require("sass"));
const cleanCSS = require("gulp-clean-css");
const uglify = require("gulp-uglify");
const rename = require("gulp-rename");
const header = require("gulp-header");
const concat = require("gulp-concat");
const fs = require("fs");
const path = require("path");

// Paths
const paths = {
  styles: {
    src: "src/stylesheets/main.scss",
    dest: "./"
  },
  scripts: {
    src: "src/scripts/javascript.js",
    dest: "js"
  }
};

// Grab WordPress theme header from style.css (before compilation)
function getThemeHeader() {
  const file = path.join(__dirname, "style.css");
  if (fs.existsSync(file)) {
    const content = fs.readFileSync(file, "utf8");
    // Match only the top comment (/* ... */)
    const match = content.match(/\/\*[\s\S]*?\*\//);
    return match ? match[0] + "\n" : "";
  }
  return "";
}

// Compile and minify SCSS
function styles() {
  const themeHeader = getThemeHeader();
  return gulp
    .src(paths.styles.src)
    .pipe(sass().on("error", sass.logError))
    .pipe(cleanCSS())
    .pipe(header(themeHeader)) // prepend theme header
    .pipe(rename("style.css"))
    .pipe(gulp.dest(paths.styles.dest));
}

// Minify JavaScript
function scripts() {
  return gulp
    .src(paths.scripts.src)
    .pipe(concat("scripts.js")) // ensure filename
    .pipe(uglify())
    .pipe(gulp.dest(paths.scripts.dest));
}

// Watch files
function watchFiles() {
  gulp.watch("src/stylesheets/**/*.scss", styles);
  gulp.watch("src/scripts/**/*.js", scripts);
}

exports.styles = styles;
exports.scripts = scripts;
exports.watch = watchFiles;
exports.default = gulp.series(styles, scripts, watchFiles);
