self.addEventListener('push', function (event) {
    if (!event.data) return;

    const data = event.data.json();

    const title = data.title || 'InCase';
    const options = {
        body: data.body || '',
        icon: '/favicon.ico',
        badge: '/favicon.ico',
        data: data.data || {},
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    event.waitUntil(
        clients.openWindow('/dashboard')
    );
});