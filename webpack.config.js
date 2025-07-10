var Encore = require('@symfony/webpack-encore');
var path = require('path');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('web/build-wp/')
    .setPublicPath('/build-wp')

    .addEntry('ixa-sorting-factors', './src/Azimut/Bundle/MontgolfiereAppBundle/Resources/js/sorting-factors.ts')
    .addEntry('ixa-automatic-affectations', './src/Azimut/Bundle/MontgolfiereAppBundle/Resources/js/automatic-affectations.ts')
    .addEntry('ixa-questionnaire', './src/Azimut/Bundle/MontgolfiereAppBundle/Resources/js/questionnaire/index.ts')
    .addEntry('ixa-segments', './src/Azimut/Bundle/MontgolfiereAppBundle/Resources/js/segments/index.ts')
    .addEntry('emails', './src/Azimut/Bundle/MontgolfiereAppBundle/Resources/scss/emails.scss')
    .addEntry('emails-noinline', './src/Azimut/Bundle/MontgolfiereAppBundle/Resources/scss/emails-noinline.scss')

    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    // enables Sass/SCSS support
    .enableSassLoader()
    .enableTypeScriptLoader()
    .enableIntegrityHashes(Encore.isProduction())

// uncomment if you're having problems with a jQuery plugin
//.autoProvidejQuery()
    .enableVueLoader()
    .configureDevServerOptions(options => {
        options.firewall = false;
        options.client = {overlay: true};
    })
    .addAliases({
        'vendor': path.resolve(__dirname, 'vendor')
    })
;

module.exports = Encore.getWebpackConfig();
