/* Variables */
var gulp  = require('gulp'),
    bs = require('browser-sync').create(),
    sass = require('gulp-sass'),
    concat = require('gulp-concat'),
    autoprefixer = require('gulp-autoprefixer'),
    babel = require('gulp-babel'),
    uglify = require('gulp-uglify'),
    uglifycss = require('gulp-uglifycss'),
    gzip = require('gulp-gzip'),
    sourcemaps = require('gulp-sourcemaps'),
    plumber = require('gulp-plumber'),
    rev = require('gulp-rev'),
    del = require('del'),
    ms = require('merge-stream'),
    rfi = require ('gulp-real-favicon'),
    fs = require('fs'),
    workboxBuild = require('workbox-build');

/* Config */
var config = require('./config.json'),
    assetsPath = config.assetsPath;

/* Tasks */
gulp.task('service-worker', () => {
    return workboxBuild.generateSW({
        globDirectory: 'public',
        globPatterns: [
            '**/build/**/*.{js.gz,css.gz,js,css,otf,eot,svg,ttf,woff,woff2,png}'
        ],
        swDest: 'public/sw.js',
        skipWaiting: true,
        clientsClaim: true,
        runtimeCaching: [
            {
                urlPattern: /\.(?:png|jpg|jpeg|svg|gif)$/,
                handler: 'staleWhileRevalidate',
                options: {
                    expiration: {
                        maxEntries: 50,
                    },
                    cacheableResponse: {
                        statuses: [0, 200]
                    }
                },
            },/*
            // Change the Domain for external fetched data
            {
                urlPattern: new RegExp('^https://cors\.example\.com/'),
                handler: 'staleWhileRevalidate',
                options: {
                    cacheName: 'sitecache',
                    expiration: {
                        maxEntries: 5,
                        maxAgeSeconds: 60,
                    },
                    cacheableResponse: {
                        statuses: [0, 200],
                        headers: {'x-test': 'true'},
                    }
                }
            }*/
        ],
    });
});

gulp.task('fonts', function () {
    return gulp.src(config.fonts.src)
        .pipe(gulp.dest(config.publicPath + '/' + config.fonts.dest));
});

gulp.task('favicon-generate', function (done) {
    return favicon(done);
});

gulp.task('inject-favicon-markups', function() {
    return gulp.src(config.favicon.htmlSrc, { allowEmpty: true })
        .pipe(rfi.injectFaviconMarkups(JSON.parse(fs.readFileSync(config.favicon.data)).favicon.html_code))
        .pipe(gulp.dest(config.favicon.htmlDest));
});

gulp.task('favicon', gulp.series(
    'favicon-generate',
    'inject-favicon-markups'
));

gulp.task('styles', function () {
    return styles(config.styles.frontend);
});

gulp.task('scripts', function() {
    return scripts(config.scripts.frontend);
});

gulp.task('clean:styles', function () {
    return del([assetsPath + '/build/css/*']);
});

gulp.task('clean:scripts', function () {
    return del([assetsPath + '/build/js/*']);
});

gulp.task('clean', gulp.series(
    'clean:styles',
    'clean:scripts'
));

gulp.task('watch', function() {
    gulp.watch(assetsPath + '/sass/**',
        gulp.series('clean:styles', 'styles'));

    gulp.watch(assetsPath + '/js/**',
        gulp.series('clean:scripts', 'scripts'));
});

gulp.task('watch:bs', function() {
    bs.init({
        proxy: config.bsProxy
    });

    gulp.watch(assetsPath + '/sass/**',
        gulp.series('clean:styles', 'styles'))
        .on('change', bs.reload);

    gulp.watch(assetsPath + '/js/**',
        gulp.series('clean:scripts', 'scripts'))
        .on('change', bs.reload);

    if (config.watchHtml) {
        gulp.watch(config.watchHtml).on('change', bs.reload);
    }
});

gulp.task('default',
    gulp.series(
        'clean',
        'fonts',
        'scripts',
        'styles',
        'service-worker'
    )
);

/* Deployment tasks */
gulp.task('deploy:styles', function () {
    return deployStyles(config.styles.frontend);
});

gulp.task('deploy:scripts', function() {
    return deployScripts(config.scripts.frontend)
});

gulp.task('compress', function() {
    return ms([
        gulp.src(assetsPath + '/build/js/*.js')
            .pipe(gzip())
            .pipe(gulp.dest(assetsPath + '/build/js')),
        gulp.src(assetsPath + '/build/css/*.css')
            .pipe(gzip())
            .pipe(gulp.dest(assetsPath + '/build/css'))
    ]);
});

gulp.task('deploy',
    gulp.series(
        'clean',
        'fonts',
        'favicon',
        'deploy:scripts',
        'deploy:styles',
        'compress',
        'service-worker'
    )
);

/* Functions for styles and scripts */
function styles(conf) {
    return gulp.src(conf.src)
    .pipe(plumber(function (error) {
        console.log(error.toString());
        this.emit('end');
    }))
    .pipe(sourcemaps.init())
    .pipe(sass())
    .pipe(concat(conf.dest))
    .pipe(rev())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(config.publicPath))
    .pipe(rev.manifest({merge: true}))
    .pipe(gulp.dest('.'));
}

function scripts(conf) {
    return gulp.src(conf.src)
    .pipe(plumber(function (error) {
        console.log(error.toString());
        this.emit('end');
    }))
    .pipe(sourcemaps.init())
    .pipe(concat(conf.dest))
    .pipe(rev())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(config.publicPath))
    .pipe(rev.manifest({merge: true}))
    .pipe(gulp.dest('.'));
}

function deployStyles(conf) {
    return gulp.src(conf.src)
    .pipe(plumber(function (error) {
        console.log(error.toString());
        this.emit('end');
    }))
    .pipe(sass())
    .pipe(autoprefixer())
    .pipe(concat(conf.dest))
    .pipe(uglifycss())
    .pipe(rev())
    .pipe(gulp.dest(config.publicPath))
    .pipe(rev.manifest({merge: true}))
    .pipe(gulp.dest('.'));
}

function deployScripts(conf) {
    return gulp.src(conf.src)
    .pipe(plumber(function (error) {
        console.log(error.toString());
        this.emit('end');
    }))
    .pipe(concat(conf.dest))
    .pipe(babel({
        presets: ['@babel/env']
    }))
    .pipe(uglify())
    .pipe(rev())
    .pipe(gulp.dest(config.publicPath))
    .pipe(rev.manifest({merge: true}))
    .pipe(gulp.dest('.'));
}

function favicon(done) {
    return rfi.generateFavicon({
        masterPicture: config.favicon.src,
        dest: config.favicon.dest,
        iconsPath: config.favicon.assets,
        design: {
            ios: {
                pictureAspect: 'noChange',
                assets: {
                    ios6AndPriorIcons: false,
                    ios7AndLaterIcons: false,
                    precomposedIcons: false,
                    declareOnlyDefaultIcon: true
                }
            },
            desktopBrowser: {},
            windows: {
                pictureAspect: 'noChange',
                backgroundColor: '#2b5797',
                onConflict: 'override',
                assets: {
                    windows80Ie10Tile: false,
                    windows10Ie11EdgeTiles: {
                        small: false,
                        medium: true,
                        big: false,
                        rectangle: false
                    }
                }
            },
            androidChrome: {
                pictureAspect: 'noChange',
                themeColor: '#3da1d9',
                manifest: {
                    display: 'standalone',
                    orientation: 'notSet',
                    onConflict: 'override',
                    declared: true
                },
                assets: {
                    legacyIcon: false,
                    lowResolutionIcons: false
                }
            },
            safariPinnedTab: {
                pictureAspect: 'silhouette',
                themeColor: '#5bbad5'
            }
        },
        settings: {
            scalingAlgorithm: 'Mitchell',
            errorOnImageTooSmall: false,
            readmeFile: false,
            htmlCodeFile: false,
            usePathAsIs: false
        },
        markupFile: config.favicon.data
    }, function () {
        done();
    });
}
