import gulp from 'gulp';

const {src, dest, watch, series, parallel} = gulp;

import yargs from 'yargs';
import {hideBin} from 'yargs/helpers';

const argv = yargs(hideBin(process.argv)).argv;

import gulpSass from 'gulp-sass';
import * as dartSass from 'sass';

const sass = gulpSass(dartSass);

import gulpCleanCSS from 'gulp-clean-css';
import rename from 'gulp-rename';
import gulpif from 'gulp-if';
import sourcemaps from 'gulp-sourcemaps';
import autoprefixer from 'gulp-autoprefixer';
import postcss from 'gulp-postcss';
import {deleteAsync} from 'del';
import rigger from 'gulp-rigger';
import plumber from 'gulp-plumber';
import webpackStream from 'webpack-stream';
import webpackConfig from './webpack.config.js';

// Environment flag
const isDevelopment = !argv.prod;

const paths = {
    dst: {
        js: '../frontend-public/assets/js/',
        css: '../frontend-public/assets/css/',
        images: '../frontend-public/assets/images/',
        fonts: '../frontend-public/assets/fonts/'
    },
    src: {
        js: ['./js/*.js', '!./src/js/appEntry.js'],
        appJs: './js/appEntry.js',
        css: './scss/*.scss',
        images: './images/**/*',
        fonts: './fonts/*'
    },
    watch: {
        js: ['./src/js/**/*.js', './src/js/**/*.jsx'],
        css: ['./src/scss/*.scss', './src/scss/**/*.scss'],
        images: './src/images/**/*',
        fonts: './src/fonts/*'
    },
    minify: {
        // CHANGED: Updated minify paths to match new output structure
        js: ['../frontend-public/assets/js/*.js', '!../frontend-public/assets/js/*.min.js'],
        css: ['../frontend-public/assets/css/*.css', '!../frontend-public/assets/css/*.min.css'],
        images: '../frontend-public/assets/images/*'
    },
    clean: {
        js: '../frontend-public/assets/js/*',
        css: '../frontend-public/assets/css/*',
        images: '../frontend-public/assets/images/*',
        fonts: '../frontend-public/assets/fonts/*'
    }
};

// Clean tasks

function cleanCSS() {
    return deleteAsync([paths.clean.css], {force: true});
}

function cleanJS() {
    return deleteAsync([paths.clean.js], {force: true});
}

function cleanImages() {
    return deleteAsync([paths.clean.images], {force: true});
}

function cleanFonts() {
    return deleteAsync([paths.clean.fonts], {force: true});
}

function cleanAll() {
    return deleteAsync([paths.clean.css, paths.clean.js, paths.clean.images, paths.clean.fonts], {force: true});
}

/**
 * Build tasks for all assets
 */

// Images optimization and copy task
function buildImages() {
    return src(paths.src.images).pipe(plumber()).pipe(dest(paths.dst.images));
}

function buildFonts() {
    return src(paths.src.fonts).pipe(dest(paths.dst.fonts));
}

function buildCSS() {
    const DO_POSTCSS = !isDevelopment;
    const DO_MINIFY = !isDevelopment;
    return (
        src(paths.src.css)
            // Using plumber to handle errors gracefully
            .pipe(
                plumber({
                    errorHandler: function (err) {
                        console.log('SCSS Error:', err.message);
                        this.emit('end');
                    }
                })
            )
            .pipe(sourcemaps.init())

            // The gulp-sass plugin compiles SCSS files to CSS.
            .pipe(
                sass({
                    outputStyle: isDevelopment ? 'expanded' : 'compressed',
                    includePaths: ['node_modules']
                }).on('error', sass.logError)
            )

            // The autoprefixer plugin adds vendor prefixes to CSS rules using values from `Can I Use`.
            .pipe(
                autoprefixer({
                    cascade: false
                })
            )

            // This line processes the CSS with PostCSS, allowing for additional transformations.
            // .pipe(postcss([ autoprefixer() ]))
            .pipe(postcss())
            // Save compressed version map
            .pipe(sourcemaps.write('./'))
            // Save uncompressed version
            .pipe(dest(paths.dst.css))

            // Below is compressed version flow
            .pipe(gulpif(DO_MINIFY, rename({suffix: '.min'})))
            .pipe(
                gulpif(
                    DO_MINIFY,
                    gulpCleanCSS(
                        {
                            compatibility: '*',
                            debug: true
                        },
                        details => {
                            if (details.stats) {
                                console.log(`CSS: ${details.name} - Original: ${details.stats.originalSize}b, Minified: ${details.stats.minifiedSize}b`);
                            }
                        }
                    )
                )
            )
            // Save compressed version map
            .pipe(gulpif(DO_MINIFY, sourcemaps.write('./')))
            // Save compressed version
            .pipe(gulpif(DO_MINIFY, dest(paths.dst.css)))
    );
}

function buildJS() {
    return (
        src(paths.src.js)
            .pipe(
                plumber({
                    errorHandler: function (err) {
                        console.log('JS Error:', err.message);
                        this.emit('end');
                    }
                })
            )
            .pipe(rigger())
            .pipe(
                webpackStream(webpackConfig, null, (err, stats) => {
                    if (err) {
                        console.log('Webpack Error:', err);
                        return;
                    }

                    if (stats.hasErrors()) {
                        console.log('Webpack compilation errors:');
                        console.log(stats.toString({colors: true, errors: true, warnings: false}));
                        return;
                    }

                    if (isDevelopment) {
                        console.log(
                            stats.toString({
                                colors: true,
                                chunks: false,
                                chunkModules: false,
                                modules: false,
                                assets: true
                            })
                        );
                    }
                })
            )
            // Save compressed version
            .pipe(dest(paths.dst.js))
    );
}

/**
 * Individual tasks for CSS, JS, and Images
 */

function taskCSS(done) {
    console.log('🔄 Running CSS tasks...');
    series(cleanCSS, buildCSS)(done);
}

function taskJS(done) {
    console.log('🔄 Running JS tasks...');
    series(cleanJS, buildJS)(done);
}

function taskImages(done) {
    console.log('🔄 Running Images tasks...');
    series(cleanImages, buildImages)(done);
}

function taskFonts(done) {
    console.log('🔄 Running Fonts tasks...');
    series(cleanFonts, buildFonts)(done);
}

/**
 * Watch tasks for all assets
 */

export const watchCss = () => {
    let options = {
        ignoreInitial: false,
        delay: 200,
        events: 'all'
    };
    watch(paths.watch.css, options, taskCSS);
};

export const watchJs = () => {
    let options = {
        ignoreInitial: false,
        delay: 200,
        events: 'all'
    };
    watch(paths.watch.js, options, taskJS);
};

export const watchImages = () => {
    let options = {
        ignoreInitial: false,
        delay: 200,
        events: 'all'
    };
    watch(paths.watch.images, options, taskImages);
};

export const watchAll = () => {
    let options = {
        ignoreInitial: false,
        delay: 500,
        events: 'all'
    };
    console.log('👀 Watching all files for changes...');
    watch(paths.watch.css, options, taskCSS);
    watch(paths.watch.js, options, taskJS);
    watch(paths.watch.images, options, taskImages);
};

export const runAll = done => {
    series(taskCSS, taskJS, taskImages, taskFonts)(done);
};

export default runAll;
