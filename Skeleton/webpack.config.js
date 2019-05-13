const Encore = require('@symfony/webpack-encore');
const config = require('./config.json');
const CompressionPlugin = require('compression-webpack-plugin');
const WorkboxPlugin = require('workbox-webpack-plugin');

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
    // SW Generation
    Encore.addPlugin(
        new WorkboxPlugin.GenerateSW({
            globDirectory: config.assetsPath,
            globPatterns: [
                '**/*.{gz,js,css,jpg,JPG,png,ico,otf,eot,ttf,woff,woff2,svg}'
            ],
            runtimeCaching: [
                {
                    urlPattern: new RegExp('/de/(.*)'),
                    handler: 'staleWhileRevalidate'
                },
                {
                    urlPattern: new RegExp('/en/(.*)'),
                    handler: 'staleWhileRevalidate'
                }
            ],
            swDest: '../../sw.js'
        })
    );
    // GZip Compression
    Encore.addPlugin(
        new CompressionPlugin({
            test: /\.(js|css)$/,
            cache: true
        })
    );
}

module.exports = Encore.getWebpackConfig();
