
import './bootstrap';

import { createApp } from 'vue';
import App from './App.vue';
import PublicMap from './PublicMap.vue';

createApp(['/', '/publik'].includes(window.location.pathname) ? PublicMap : App).mount('#app');
