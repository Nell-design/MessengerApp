import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});


// import Echo from 'laravel-echo';
// import Pusher from 'pusher-js';

// // Configuration détaillée d'Echo
// window.Pusher = Pusher;

// const echoConfig = {
//     broadcaster: 'reverb',
//     key: 'your-reverb-app-key', // Clé par défaut pour le développement
//     wsHost: '127.0.0.1',
//     wsPort: 8080,
//     wssPort: 8080,
//     forceTLS: false,
//     enabledTransports: ['ws', 'wss'],
//     disableStats: true,
//     auth: {
//         headers: {
//             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
//         },
//     },
// };

// console.log('🔧 Configuration Echo:', echoConfig);

// window.Echo = new Echo(echoConfig);

// Logs détaillés pour le debugging
// window.Echo.connector.pusher.connection.bind('connected', () => {
//     console.log('✅ Connexion Echo établie avec succès');
//     console.log('🔍 Echo: État de la connexion:', window.Echo.connector.pusher.connection.state);
//     console.log('🔍 Echo: Socket ID:', window.Echo.connector.pusher.connection.socket_id);
// });

// window.Echo.connector.pusher.connection.bind('disconnected', () => {
//     console.log('❌ Connexion Echo perdue');
//     console.log('🔍 Echo: État de la connexion:', window.Echo.connector.pusher.connection.state);
// });

// window.Echo.connector.pusher.connection.bind('error', (error) => {
//     console.error('❌ Erreur de connexion Echo:', error);
//     console.log('🔍 Echo: État de la connexion:', window.Echo.connector.pusher.connection.state);
// });

// // Intercepter tous les événements pour le debugging
// const originalListen = window.Echo.channel;
// window.Echo.channel = function(channelName) {
//     console.log('🔗 Tentative de connexion au canal:', channelName);
//     console.log('🔍 Echo: État de la connexion avant canal:', window.Echo.connector.pusher.connection.state);
    
//     const channel = originalListen.call(this, channelName);
    
//     // Intercepter les événements sur ce canal
//     const originalChannelListen = channel.listen;
//     channel.listen = function(eventName, callback) {
//         console.log('👂 Ajout du listener pour l\'événement:', eventName, 'sur le canal:', channelName);
//         console.log('🔍 Echo: Canal object:', channel);
//         console.log('🔍 Echo: État de la connexion lors de l\'ajout du listener:', window.Echo.connector.pusher.connection.state);
        
//         return originalChannelListen.call(this, eventName, function(data) {
//             console.log('📨 Événement reçu:', eventName, 'sur le canal:', channelName, 'avec les données:', data);
//             console.log('🔍 Echo: État de la connexion lors de la réception:', window.Echo.connector.pusher.connection.state);
//             callback(data);
//         });
//     };
    
//     // Intercepter la souscription au canal
//     const originalSubscribe = channel.subscribe;
//     channel.subscribe = function() {
//         console.log('🔔 Souscription au canal:', channelName);
//         console.log('🔍 Echo: État de la connexion lors de la souscription:', window.Echo.connector.pusher.connection.state);
//         return originalSubscribe.call(this);
//     };
    
//     return channel;
// };

// console.log('🚀 Echo initialisé avec debugging activé');
