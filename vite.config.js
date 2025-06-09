import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                compilerOptions: {
                    isCustomElement: (tag) => ['dc:format','dc:type','cc:license','dc.title','cc:Agent','dc:publisher','cc:Work','dc:title','cc:permits','cc:License','rdf:RDF'].includes(tag),
                }
            }
        }),
    ],
});
