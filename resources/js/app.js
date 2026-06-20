import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import axios from 'axios';

// Composable de tema para la inicialización
import { useTheme } from '@/Composables/useTheme';

// Configuración de Identidad de Invitado (Cero latencia en peticiones)
const guestUuid = localStorage.getItem('guest_client_uuid');
if (guestUuid) {
    axios.defaults.headers.common['X-Guest-UUID'] = guestUuid;
}

const appName = import.meta.env.VITE_APP_NAME || 'CyberMarket';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        // --- SINCRONIZACIÓN TÁCTICA DEL TEMA ---
        const { initTheme } = useTheme();
        initTheme(); 

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#f97316', // Rojo F1 para la barra de carga
        showSpinner: false,
    },
});