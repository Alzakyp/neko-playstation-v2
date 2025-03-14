const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .postCss('resources/css/app.css', 'public/css', [
       // Add PostCSS plugins here if needed
   ])
   .copyDirectory('resources/assets', 'public/assets');

