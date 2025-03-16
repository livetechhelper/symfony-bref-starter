/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';
import { createApp } from 'vue';

// Create a new Vue app
const app = createApp({
    data() {
        return {
            message: 'Hello from Vue.js!'
        };
    },
    template: `<div>{{ message }}</div>`
});

// Mount the Vue app to an element with id 'vue-app'
app.mount('#vue-app');

console.log('Symfony Bref Starter - Frontend initialized with Vue.js! 🎉');
