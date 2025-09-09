const gulp = require("gulp");
const sass = require("gulp-sass")(require("sass"));
const cleanCSS = require("gulp-clean-css");
const uglify = require("gulp-uglify");
const rename = require("gulp-rename");
const header = require("gulp-header");
const concat = require("gulp-concat");
const sourcemaps = require("gulp-sourcemaps");
const fs = require("fs");
const path = require("path");

// ---------------------------
// TO RUN: npx gulp
// ---------------------------


// ---------------------------
// CONFIGURATION
// ---------------------------

// Paths
const paths = {
  styles: {
    src: "src/stylesheets/main.scss",
    dest: "css" // <- change output to css folder
  },
  scripts: {
    src: "src/scripts/javascript.js",
    dest: "js"
  }
};

// Environment: dev vs prod
const isProduction = process.env.NODE_ENV === "production";

// ---------------------------
// HELPERS
// ---------------------------

// Grab WordPress theme header from style.css (optional if needed)
/*
function getThemeHeader() {
  const file = path.join(__dirname, "style.css");
  if (fs.existsSync(file)) {
    const content = fs.readFileSync(file, "utf8");
    const match = content.match(/\/\*[\s\S]*?\*\//);
    return match ? match[0] + "\n" : "";
  }
  return "";
}
  */

// ---------------------------
// TASKS
// ---------------------------

// Compile SCSS -> CSS
function styles() {
  //const themeHeader = getThemeHeader(); // You may remove this if not injecting WP theme headers anymore

  let pipeline = gulp.src(paths.styles.src);

  if (!isProduction) pipeline = pipeline.pipe(sourcemaps.init());

  pipeline = pipeline
    .pipe(sass().on("error", sass.logError))
    .pipe(cleanCSS({ compatibility: "ie11" }))
    //.pipe(header(themeHeader)) // Remove this line if no header needed
    .pipe(rename("main.css")); // <- change output filename

  if (!isProduction) pipeline = pipeline.pipe(sourcemaps.write("./"));

  return pipeline.pipe(gulp.dest(paths.styles.dest)); // <- writes to css/
}

// Minify JS
function scripts() {
  let pipeline = gulp.src(paths.scripts.src);

  if (!isProduction) pipeline = pipeline.pipe(sourcemaps.init());

  pipeline = pipeline
    .pipe(concat("scripts.js"))
    .pipe(uglify());

  if (!isProduction) pipeline = pipeline.pipe(sourcemaps.write("./"));

  return pipeline.pipe(gulp.dest(paths.scripts.dest));
}

// Watch files in dev
function watchFiles() {
  gulp.watch("src/stylesheets/**/*.scss", styles);
  gulp.watch("src/scripts/**/*.js", scripts);
}

// ---------------------------
// EXPORTS
// ---------------------------

exports.styles = styles;
exports.scripts = scripts;
exports.watch = watchFiles;

// Default: dev mode
exports.default = gulp.series(styles, scripts, watchFiles);

// Production build
exports.build = (done) => {
  process.env.NODE_ENV = "production";
  return gulp.series(styles, scripts)(done);
};
