const Encore = require('@symfony/webpack-encore')
const config = require('./config.json')
const CompressionPlugin = require('compression-webpack-plugin')

Encore.setOutputPath(config.assetsPath)
    .setPublicPath(config.publicPath)
    .addEntry(config.scripts.frontend.dest, config.scripts.frontend.src)
    .addStyleEntry(config.styles.frontend.dest, config.styles.frontend.src)
    .enableSassLoader()
    .enablePostCssLoader(options => {
        options.config = {
            path: './postcss.config.js'
        }
    })
    .configureBabel(babelConfig => {
        babelConfig.presets.push('env')
    })
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

if (Encore.isProduction()) {
    Encore.addPlugin(
        new CompressionPlugin({
            test: /\.(js|css)$/,
            cache: true
        }), 10
    )
}

module.exports = Encore.getWebpackConfig()