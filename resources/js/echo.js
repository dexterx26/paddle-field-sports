import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

try {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    window.Echo.connector.pusher.connection.bind('connected', () => {
        window.reverbConnected = true;
        document.querySelectorAll('.reverb-status-indicator').forEach(el => {
            el.innerHTML = '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span> Live Real-Time Sync</span>';
        });
    });

    window.Echo.connector.pusher.connection.bind('unavailable', () => {
        window.reverbConnected = false;
    });

    window.Echo.connector.pusher.connection.bind('failed', () => {
        window.reverbConnected = false;
    });
} catch (e) {
    console.warn('Reverb WebSocket initialization notice:', e);
    window.Echo = null;
    window.reverbConnected = false;
}

// Global helper to subscribe to court availability updates
window.subscribeCourtUpdates = function (callback) {
    if (window.Echo) {
        window.Echo.channel('courts')
            .listen('.CourtSlotsUpdated', (data) => {
                if (typeof callback === 'function') {
                    callback(data);
                }
            })
            .listen('CourtSlotsUpdated', (data) => {
                if (typeof callback === 'function') {
                    callback(data);
                }
            });
    }
};
