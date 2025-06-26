import gulp from 'gulp';

const {src, dest, watch, series, parallel} = gulp;

import gulpSass from 'gulp-sass';
import * as dartSass from 'sass';

const sass = gulpSass(dartSass);

import cleanCSS from 'gulp-clean-css';
import rename from 'gulp-rename';
import sourcemaps from 'gulp-sourcemaps';
import autoprefixer from 'gulp-autoprefixer';
import postcss from 'gulp-postcss';
import {deleteAsync} from 'del';
import rigger from 'gulp-rigger';
import plumber from 'gulp-plumber';
import webpackStream from 'webpack-stream';
import webpackConfig from './webpack.config.js';

// Environment flag
const isDevelopment = process.env.NODE_ENV !== 'production';
// const PRODUCTION = true;

const paths = {
    dst: {
        // CHANGED: Output to frontend-public/assets/ instead of assets/
        js: '../frontend-public/assets/js/',
        css: '../frontend-public/assets/css/',
        images: '../frontend-public/assets/images/'
    },
    src: {
        // CHANGED: Updated source paths to match new structure
        js: [
            './src/js/*.js',
            '!./src/js/appEntry.js',
        ],
        appJs: './src/js/appEntry.js',
        css: './src/scss/*.scss',
        images: './src/images/**/*'
    },
    watch: {
        // CHANGED: Updated watch paths to include all JS files in subdirectories
        js: [
            './src/js/**/*.js',
            './src/js/**/*.jsx'
        ],
        css: [
            './src/scss/*.scss',
            './src/scss/**/*.scss'
        ],
        // ADDED: Watch images
        images: './src/images/**/*'
    },
    minify: {
        // CHANGED: Updated minify paths to match new output structure
        js: [
            '../frontend-public/assets/js/*.js',
            '!../frontend-public/assets/js/*.min.js'
        ],
        css: [
            '../frontend-public/assets/css/*.css',
            '!../frontend-public/assets/css/*.min.css'
        ],
        images: '../frontend-public/assets/images/*'
    },
    clean: {
        js: '../frontend-public/assets/js/*',
        css: '../frontend-public/assets/css/*',
        images: '../frontend-public/assets/images/*'
    }
};

// Path configuration
// const paths = {
//     src: {
//         scss: './src/scss/**/*.scss',
//         scssMain: './src/scss/main.scss', // Main SCSS entry point
//         js: './src/js/index.js', // Main JS entry point
//         images: './src/images/**/*',
//         fonts: './src/fonts/**/*'
//     },
//     dest: {
//         css: '../frontend-public/assets/css/',
//         js: '../frontend-public/assets/js/',
//         images: '../frontend-public/assets/images/',
//         fonts: '../frontend-public/assets/fonts/'
//     },
//     watch: {
//         scss: './src/scss/**/*.scss',
//         js: './src/js/**/*.{js,jsx}',
//         images: './src/images/**/*',
//         fonts: './src/fonts/**/*'
//     },
//     clean: {
//         css: '../frontend-public/assets/css/**/*',
//         js: '../frontend-public/assets/js/**/*',
//         images: '../frontend-public/assets/images/**/*',
//         fonts: '../frontend-public/assets/fonts/**/*'
//     }
// };

// Clean tasks
export const cleanCSS = () => deleteAsync([paths.clean.css]);
export const cleanJS = () => deleteAsync([paths.clean.js]);
export const cleanImages = () => deleteAsync([paths.clean.images]);
export const cleanFonts = () => deleteAsync([paths.clean.fonts]);
export const cleanAll = () => deleteAsync([
    paths.clean.css,
    paths.clean.js,
    paths.clean.images,
    paths.clean.fonts
]);

/**
 * Build tasks for all assets
 */

// Images optimization and copy task
export const buildImages = () => {
    return src(paths.src.images)
        .pipe(plumber())
        .pipe(dest(paths.dst.images));
};

// Fonts copy task
export const buildFonts = () => {
    return src(paths.src.fonts)
        .pipe(dest(paths.dst.fonts));
};

export const buildCSS = () => {
    return src(paths.src.css)
        // Using plumber to handle errors gracefully
        .pipe(plumber({
            errorHandler: function (err) {
                console.log('SCSS Error:', err.message);
                this.emit('end');
            }
        }))
        .pipe(sourcemaps.init())

        // The gulp-sass plugin compiles SCSS files to CSS.
        .pipe(sass({
            outputStyle: isDevelopment ? 'expanded' : 'compressed',
            includePaths: ['node_modules']
        }).on('error', sass.logError))

        // The autoprefixer plugin adds vendor prefixes to CSS rules using values from `Can I Use`.
        .pipe(autoprefixer({
            cascade: false
        }))

        // This line processes the CSS with PostCSS, allowing for additional transformations.
        // .pipe(postcss([ autoprefixer() ]))
        .pipe(postcss())
        // Save uncompressed version
        .pipe(dest(paths.dst.css))

        // Below is compressed version flow
        .pipe(rename({suffix: '.min'}))
        .pipe(cleanCSS({
            compatibility: '*',
            debug: true
        }, (details) => {
            if (details.stats) {
                console.log(`CSS: ${details.name} - Original: ${details.stats.originalSize}b, Minified: ${details.stats.minifiedSize}b`);
            }
        }))
        // Save compressed version map
        .pipe(sourcemaps.write('./maps'))
        // Save compressed version
        .pipe(dest(paths.dst.css))
}

export const buildJS = () => {
    return src(paths.src.js)
        .pipe(plumber({
            errorHandler: function (err) {
                console.log('JS Error:', err.message);
                this.emit('end');
            }
        }))
        .pipe(rigger())
        .pipe(webpackStream(webpackConfig, null, (err, stats) => {
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
                console.log(stats.toString({
                    colors: true,
                    chunks: false,
                    chunkModules: false,
                    modules: false,
                    assets: true
                }));
            }
        }))
        // Save compressed version
        .pipe(dest(paths.dst.js));
};

function jsBuild() {
    return src(paths.src.js)

}

/**
 * Individual tasks for CSS, JS, and Images
 */

export const taskCSS = series(cleanCSS, buildCSS);
export const taskJS = series(cleanJS, buildJS);
export const taskImages = series(cleanImages, buildImages);
export const taskFonts = series(cleanFonts, buildFonts);

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
}

export const watchJs = () => {
    let options = {
        ignoreInitial: false,
        delay: 200,
        events: 'all'
    };
    watch(paths.watch.js, options, taskJS);
}

export const watchImages = () => {
    let options = {
        ignoreInitial: false,
        delay: 200,
        events: 'all'
    };
    watch(paths.watch.images, options, taskImages);
}

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
}

export const runAll = () => {
    parallel(
        taskCSS,
        taskJS,
        taskImages,
        taskFonts,
    );
}

// Production build task
export const buildProduction = () => {
    process.env.NODE_ENV = 'production';
    console.log('🚀 Building for production...');
    return runAll;
};


export default runAll;

/*
# Default build
npm run gulp

# Development with watch
    npm run gulp dev

# Build only CSS
npm run gulp css

# Production build
npm run gulp prod

# Clean all assets
npm run gulp clean
*/