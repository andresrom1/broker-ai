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
    server: {
        host: '0.0.0.0',  // Para que escuche en todas las interfaces, no solo localhost
        port: 5173,       // Puerto que usás
        hmr: {
            // Cambialo a 'localhost' si accedés con localhost desde el navegador,
            // o poné la IP/hostname que usás para acceder a Vite desde afuera del contenedor
            host: 'localhost',  
        },
    },
});
