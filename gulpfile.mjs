import gulp from 'gulp';
const {watch, series, src, dest} = gulp;

import nodeSass from 'node-sass';
import gulpSass from 'gulp-sass';
const sass = gulpSass(nodeSass);

import uglify from 'gulp-uglify-es';
import cleanCSS from  'gulp-clean-css';
import rename from 'gulp-rename';
import sourcemaps from 'gulp-sourcemaps';
import autoprefixer from 'gulp-autoprefixer';
import {deleteAsync} from 'del';
import rigger from 'gulp-rigger';
import plumber from 'gulp-plumber';

let autoprefixerOptions = [
    '>= 1%', 'last 2 major version'
];

let path = {
    dst: {
        // js: 'assets/js/',
        css: 'assets/css/'
    },
    src: {
        // js: './src/js/*.js',
        css: './src/scss/*.scss'
    },
    minify: {
        // js: [
        //     './assets/js/*.js',
        //     '!./assets/js/*.min.js'
        // ],
        css: [
            './assets/css/*.css',
            '!./assets/css/*.min.css'
        ]
    },
    clean: {
        // js: './assets/js/*',
        css: './assets/css/*'
    }
};

function cssClean() {
    // return del( path.clean.css );
    return deleteAsync( path.clean.css );
}

function cssBuild() {
    return src( path.src.css )
        .pipe( sass( {
            errLogToConsole: true,
            outputStyle: 'expanded'
        } ).on( 'error', sass.logError ) )
        .pipe( autoprefixer( autoprefixerOptions ) )
        // Save uncompressed version
        .pipe( dest( path.dst.css ) )

        // Below is compressed version flow
        .pipe( rename( {suffix: '.min'} ) )
        .pipe( sourcemaps.init() )
        .pipe( cleanCSS( {
            compatibility: 'ie9',
            debug: true
        }, ( details ) => {
            console.log( `${ details.name }: ${ details.stats.originalSize }` );
            console.log( `${ details.name }: ${ details.stats.minifiedSize }` );
        } ) )
        // Save compressed version map
        .pipe( sourcemaps.write( './' ) )
        // Save compressed version
        .pipe( dest( path.dst.css ) );
}

function jsClean() {
    return del( path.clean.js );
}

function jsBuild( cb ) {
    return src( path.src.js )
        .pipe( plumber() )
        .pipe( rigger() )
        .pipe( dest( path.dst.js ) )
        .pipe( rename( {suffix: '.min'} ) )
        .pipe( sourcemaps.init() )
        .pipe( uglify() )
        // Save compressed version map
        .pipe( sourcemaps.write( './' ) )
        // Save compressed version
        .pipe( dest( path.dst.js ) );
}

// exports.js = series( jsClean, jsBuild );
// exports.css = series( cssClean, cssBuild );
export function cssTask() {
    series( cssClean, cssBuild );
}

// exports.default = function() {
export function defaultTask() {
    let options = {
        ignoreInitial: false,
        delay: 1000,
        events: 'all'
    };
    watch( [
        'src/scss/*.scss'
    ], options, series( cssClean, cssBuild ) );
    // watch( [
    //     'src/js/*.js'
    // ], options, series( jsClean, jsBuild ) );
}

export default defaultTask
