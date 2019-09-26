const Encore = require('@symfony/webpack-encore');
const config = require('./config.json');
const CompressionPlugin = require('compression-webpack-plugin');
const WorkboxPlugin = require('workbox-webpack-plugin');

Encore.setOutputPath(config.modernAssetsPath)
      .setPublicPath(config.modernPublicPath)
      .addEntry(config.scripts.frontend.modern, config.scripts.frontend.src)
      .addStyleEntry(config.styles.frontend.dest, config.styles.frontend.src)
      .enableSassLoader()
      .enablePostCssLoader(options => {
          options.config = {
              path: './postcss.config.js'
          };
      })
      .configureBabel(babelConfig => {
          babelConfig.presets.splice(0, babelConfig.presets.length);

          babelConfig.presets.push(['@babel/preset-env', {
              'targets': {
                  'esmodules': true
              }
          }]);
      },{
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
      .enableSingleRuntimeChunk()
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
}
module.exports = Encore.getWebpackConfig();
