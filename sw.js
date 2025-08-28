self.addEventListener('push', function(event) {
    const payload = event.data ? event.data.text() : 'No payload';
    const options = {
        body: payload,
    };
    event.waitUntil(
        self.registration.showNotification('Web Push Notification', options)
    );
});