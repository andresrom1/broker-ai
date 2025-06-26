import './bootstrap';

import Alpine from 'alpinejs';
import {createApp} from 'vue/dist/vue.esm-bundler.js'
import ChatWidget from './components/ChatWidget.vue'


window.Alpine = Alpine;
Alpine.start();

// Función auxiliar para registrar el Service Worker
window.registerServiceWorker = async function() {
    if ('serviceWorker' in navigator) {
        try {
            const registration = await navigator.serviceWorker.register('/service-worker.js');
            console.log('Service Worker registrado con éxito:', registration);
            return registration;
        } catch (error) {
            console.error('Fallo el registro del Service Worker:', error);
            return null;
        }
    }
    console.warn('Los Service Workers no son soportados en este navegador.');
    return null;
};

const app = createApp({})

app.component('chat-widget', ChatWidget)

app.mount('#app')
