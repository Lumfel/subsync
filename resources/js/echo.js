import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Over HTTPS (e.g. via ngrok) the Reverb WebSocket is reverse-proxied at /app on the
// same domain, so use wss://<page-host>:443. Locally (http://subsync.test) connect
// straight to the Reverb server on ws://localhost:8080.
const isHttps = window.location.protocol === 'https:';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: isHttps ? window.location.hostname : (import.meta.env.VITE_REVERB_HOST || 'localhost'),
    wsPort: isHttps ? 443 : 8080,
    wssPort: 443,
    forceTLS: isHttps,
    enabledTransports: ['ws', 'wss'],
});
