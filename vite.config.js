import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/main.scss', 
                'resources/js/oneui/app.js',
            ],
            refresh: true,
        }),
    ],
  resolve: {
    alias: {
      jquery: 'jquery/dist/jquery.js',
    },
  },  
  build: { sourcemap: true },
});
