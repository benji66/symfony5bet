    // webpack.config.js
    
var Encore = require('@symfony/webpack-encore');
    // ...
     Encore
             // the project directory where all compiled assets will be stored
    	.setOutputPath('public/build/')
    // the public path used by the web server to access the previous directory
    	.setPublicPath('/build')    
         // uncomment to define the assets of the project
        .addEntry('app', ['./assets/js/app.js'])
        .enableSingleRuntimeChunk()
        .cleanupOutputBeforeBuild()
        .enableBuildNotifications()
        .enableSourceMaps(!Encore.isProduction())
        .enableVersioning(Encore.isProduction())
        .enableReactPreset()

        .configureBabel(function (babelConfig) {
        babelConfig.plugins = [
            "@babel/plugin-proposal-object-rest-spread","@babel/plugin-proposal-class-properties",
            "@babel/plugin-transform-runtime"
        ]
    })
;

 module.exports = Encore.getWebpackConfig();        