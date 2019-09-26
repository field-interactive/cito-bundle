const Encore = require('@symfony/webpack-encore');
const config = require('./config.json');
const CompressionPlugin = require('compression-webpack-plugin');

Encore.setOutputPath(config.assetsPath)
      .setPublicPath(config.publicPath)
      .setManifestKeyPrefix('legacy/')
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
}

module.exports = Encore.getWebpackConfig();
