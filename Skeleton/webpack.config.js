const Encore = require('@symfony/webpack-encore');
const config = require('./config.json');
const CompressionPlugin = require('compression-webpack-plugin');
const WorkboxPlugin = require('workbox-webpack-plugin');
const WebappWebpackPlugin = require('webapp-webpack-plugin');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const path = require('path');

Encore.setOutputPath(config.assetsPath)
      .setPublicPath(config.publicPath)
      .addEntry(config.scripts.frontend.dest, config.scripts.frontend.src)
      .addStyleEntry(config.styles.frontend.dest, config.styles.frontend.src)
      .enableSassLoader()
      .enablePostCssLoader(options => {
          options.config = {
              path: './postcss.config.js'
          };
      })
      .configureBabel(() => {}, {
          useBuiltIns: 'usage',
          corejs: 3
      })
      .splitEntryChunks()
      .configureSplitChunks(splitChunks => {
          splitChunks.minSize = 0;
      })
      .configureTerserPlugin(options => {
          options.cache = true;
          options.terserOptions = {
              output: {
                  comments: false
              }
          };
      })
      .disableSingleRuntimeChunk()
      .autoProvidejQuery()
      .cleanupOutputBeforeBuild()
      .enableBuildNotifications()
      .enableSourceMaps(!Encore.isProduction())
      .enableVersioning(Encore.isProduction());

if (Encore.isProduction()) {
    // GZip Compression
    Encore.addPlugin(
        new CompressionPlugin({
            test: /\.(js|css)$/,
            cache: true
        })
    );
    // SW Generation
    Encore.addPlugin(
        new WorkboxPlugin.GenerateSW({
            globDirectory: 'public',
            clientsClaim: true,
            skipWaiting: true,
            globPatterns: [
                '**/*.{gz,js,css,jpg,JPG,png,ico,otf,eot,ttf,woff,woff2,svg}'
            ],
            runtimeCaching: [
                {
                    urlPattern: new RegExp('https://cdnjs.cloudflare.com/'),
                    handler: 'StaleWhileRevalidate'
                }
            ],
            swDest: '../sw.js'
        })
    );
    // Generate HTML for Favicons
    Encore.addPlugin(
        new HtmlWebpackPlugin({
            filename: path.join(__dirname, 'templates/partials/favicons.html.twig'),
            template: path.join(__dirname, 'templates/faviconsTmpl.html.twig'),
            inject: true,
            minify: false,
            excludeChunks: ['css/main', 'js/main']
        })
    );
    // Generate Favicons
    Encore.addPlugin(
        new WebappWebpackPlugin({
            logo: path.join(__dirname, config.icon.path),
            cache: true,
            publicPath: '/build/icons',
            outputPath: 'icons',
            prefix: '',
            inject: true,
            orientation: 'portrait',
            favicons: {
                appName: null,
                appDescription: null,
                developerName: null,
                developerURL: null,
                theme_color: null,
                lang: null,
                start_url: null,
                icons: {
                    background: false,
                    android: false,
                    appleIcon: false,
                    appleStartup: false,
                    coast: false,
                    favicons: true,
                    firefox: false,
                    opengraph: false,
                    twitter: false,
                    yandex: false,
                    windows: false
                }
            }
        })
    );
}

const standardConfig = Encore.getWebpackConfig();

standardConfig.name = 'standardConfig';
standardConfig.watchOptions = {
    poll: true,
    ignored: /node_modules/
};

// Requires symfony server to be configured to serve on localhost:8000
standardConfig.devServer = {
    public: 'localhost:8000',
    allowedHosts: ['localhost:8000'],
    contentBase: path.join(__dirname, 'public/'),
    watchContentBase: true,
    compress: true,
    open: true,
    disableHostCheck: true,
    progress: true,
    watchOptions: {
        watch: true,
        poll: true
    }
};

Encore.reset();

Encore.setOutputPath(config.assetsPath)
      .setPublicPath(config.publicPath)
      .addEntry(config.scripts.frontend.dest, config.scripts.frontend.src)
      .addStyleEntry(config.styles.frontend.dest, config.styles.frontend.src)
      .enableSassLoader()
      .enablePostCssLoader(options => {
          options.config = {
              path: './postcss.config.js'
          };
      })
      .configureBabel(() => {}, {
          useBuiltIns: 'usage',
          corejs: 3
      })
      .splitEntryChunks()
      .configureSplitChunks(splitChunks => {
          splitChunks.minSize = 0;
      })
      .configureTerserPlugin(options => {
          options.cache = true;
          options.terserOptions = {
              output: {
                  comments: false
              }
          };
      })
      .disableSingleRuntimeChunk()
      .autoProvidejQuery()
      .cleanupOutputBeforeBuild()
      .enableBuildNotifications()
      .enableSourceMaps(!Encore.isProduction())
      .enableVersioning(Encore.isProduction());

if (Encore.isProduction()) {
    // GZip Compression
    Encore.addPlugin(
        new CompressionPlugin({
            test: /\.(js|css)$/,
            cache: true
        })
    );
    // SW Generation
    Encore.addPlugin(
        new WorkboxPlugin.GenerateSW({
            globDirectory: 'public',
            clientsClaim: true,
            skipWaiting: true,
            globPatterns: [
                '**/*.{gz,js,css,jpg,JPG,png,ico,otf,eot,ttf,woff,woff2,svg}'
            ],
            runtimeCaching: [
                {
                    urlPattern: new RegExp('/*(.*)'),
                    handler: 'StaleWhileRevalidate'
                },
                {
                    urlPattern: new RegExp('https://cdnjs.cloudflare.com/'),
                    handler: 'StaleWhileRevalidate'
                }
            ],
            swDest: '../sw.js'
        })
    );
    // Generate HTML for Favicons + PWA
    Encore.addPlugin(
        new HtmlWebpackPlugin({
            filename: path.join(__dirname, 'templates/partials/favicons.html.twig'),
            template: path.join(__dirname, 'templates/faviconsTmpl.html.twig'),
            inject: true,
            minify: false,
            excludeChunks: ['css/main', 'js/main']
        })
    );
// Generate Favicons + PWA stuff
    Encore.addPlugin(
        new WebappWebpackPlugin({
            logo: path.join(__dirname, config.icon.path),
            cache: true,
            publicPath: '/build/icons',
            outputPath: 'icons',
            prefix: '',
            inject: true,
            orientation: 'portrait',
            favicons: {
                appName: config.icon.name,
                appDescription: config.icon.description,
                developerName: '',
                developerURL: config.icon.devURL,
                background: config.icon.backgroundColor,
                theme_color: config.icon.themeColor,
                lang: 'de-DE',
                start_url: '/',
                icons: {
                    coast: false,
                    yandex: false
                }
            }
        })
    );
}

const pwa = Encore.getWebpackConfig();
pwa.name = 'pwa';

module.exports = [standardConfig, pwa];
