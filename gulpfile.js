var elixir = require('laravel-elixir');
elixir.config.js.babel.enabled = false;
/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    mix.browserify(['teacher/app.js'],'public/js/teacher/bundle.js')
        .browserify(['student/app.js'],'public/js/student/bundle.js')
        .browserify(['config.js'], 'public/js/config.js')
        .sass('app.scss')
        .styles([
            'public/css/app.css',
            'resources/assets/vendor/icons/style.css'
        ], 'public/css/app.css', './')
        .version(['css/app.css', 'js/teacher/bundle.js', 'js/student/bundle.js','js/config.js'])
        .copy('./resources/assets/vendor/font-awesome/fonts','public/fonts')
        .copy('./resources/assets/vendor/icons/fonts/','public/fonts')
});