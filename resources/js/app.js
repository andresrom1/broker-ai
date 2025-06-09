import './bootstrap';

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();


import {createApp} from 'vue/dist/vue.esm-bundler.js'
const app = createApp({})

import ChatWidget from './components/ChatWidget.vue'
app.component('chat-widget', ChatWidget)

app.mount('#app')