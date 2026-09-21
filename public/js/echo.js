import Echo from "laravel-echo";
window.Pusher = require('pusher-js');

export default function EchoInit() {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: window.LARAVEL_ECHO_CONFIG?.key || '',
        cluster: window.LARAVEL_ECHO_CONFIG?.cluster || 'mt1',
        encrypted: true,
        wsHost: window.location.hostname,
        wsPort: 6001,
        wssPort: 6001,
        forceTLS: false,
        disableStats: true,
    });

    // Listen for HOA expenses updates
    window.Echo.channel('hoa-expenses')
        .listen('.hoa-expenses-updated', (e) => {
            console.log('Received HOA expenses update:', e);
            // Trigger a refresh of the HOA expenses display
            if (window.loadHOAExpenses) {
                window.loadHOAExpenses();
            }
        });
}
