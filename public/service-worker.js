// public/service-worker.js

// Escucha el evento 'push' (cuando el servidor envía una notificación push)
self.addEventListener('push', function(event) {
    let data = {};
    if (event.data) {
        // Intenta parsear los datos de la notificación.
        // Si no es JSON válido, se usa como texto plano.
        try {
            data = event.data.json();
            console.log('Push data (parsed JSON):', data);
        } catch (e) {
            data = { message: event.data.text() }; // Si no es JSON, toma el texto
            console.log('Push data (plain text):', data.message);
        }
    }

    const title = data.title || 'Nueva Notificación';
    const options = {
        body: data.body || data.message || 'Tienes un nuevo mensaje.',
        icon: '/favicon.ico', // Puedes cambiar esto por la URL de tu icono
        badge: '/favicon.ico', // Pequeño icono en dispositivos móviles
        image: data.image || null, // URL de una imagen grande
        data: {
            url: data.url || '/', // URL a abrir cuando se haga clic en la notificación
            // Puedes añadir más datos personalizados aquí
        },
        actions: data.actions || [] // Botones de acción en la notificación
    };

    console.log('Showing notification with title:', title, 'and options:', options);

    // Muestra la notificación al usuario
    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Escucha el evento 'notificationclick' (cuando el usuario hace clic en la notificación)
self.addEventListener('notificationclick', function(event) {
    event.notification.close(); // Cierra la notificación al hacer clic

    // Abre la URL asociada a la notificación
    const urlToOpen = event.notification.data.url || '/';

    event.waitUntil(
        clients.openWindow(urlToOpen)
        // Puedes intentar enfocar una ventana existente en lugar de abrir una nueva
        /*
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(windowClients => {
            const client = windowClients.find(client => {
                return client.url.includes(urlToOpen) || client.url === self.location.origin + '/';
            });

            if (client) {
                return client.focus();
            } else {
                return clients.openWindow(urlToOpen);
            }
        })
        */
    );
});
