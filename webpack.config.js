
const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured())
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or subdirectory deploy
    //.setManifestKeyPrefix('build/')
    //Each entry results in one JavaScript file and one CSS file.
    .addEntry('store', './assets/scripts/store.js')
    .addEntry('admin', './assets/scripts/admin.js')
    .addEntry('datepicker', './assets/scripts/datepicker.js')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()
    // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
    .enableStimulusBridge('./assets/controllers.json')
    // will require an extra script tag for runtime.js
    // .enableSingleRuntimeChunk()
    .disableSingleRuntimeChunk()
    // https://symfony.com/doc/current/frontend.html#adding-more-features
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())
    // enables and configure @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23';})
    // enables Sass/SCSS support
    .enableSassLoader()
    .enablePostCssLoader()
    .autoProvideVariables({
        $: 'jquery',
        jQuery: 'jquery',
        'window.jQuery': 'jquery'})
    .copyFiles({
         from: './assets/images',
         // optional target path, relative to the output dir
         // to: 'images/[path][name].[ext]',
         // if versioning is enabled, add the file hash too
         to: 'images/[path][name].[hash:8].[ext]',
         // only copy files matching this pattern
         //pattern: /\.(png|jpg|jpeg)$/
     })
     /* The logo images without versioning are required to be loaded via javascript in brand select.  */
     .copyFiles(
        { from: './assets/images/store/color-patches',
          to: 'images/[path][name].[ext]'}
      )
     .copyFiles(
        { from: './assets/plugins/store/zoombox/images',
          to: 'images/[path][name].[ext]',}
      )
;

module.exports = Encore.getWebpackConfig();
