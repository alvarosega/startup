import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;

// 1. Cálculo dinámico de ruteo para Cloud Workstations
const currentHost = window.location.hostname;
const wsHostname = currentHost.includes('cloudworkstations.dev') 
    ? currentHost.replace('8000-', '8080-') 
    : import.meta.env.VITE_REVERB_HOST;

if (import.meta.env.VITE_REVERB_APP_KEY) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: wsHostname,
        wsPort: 80,
        wssPort: 443, // El proxy de Google expone todo por 443 (HTTPS)
        forceTLS: true, // Forzamos conexión segura para evitar bloqueos del navegador
        enabledTransports: ['wss'], // Bloqueamos conexiones no seguras
    });
}