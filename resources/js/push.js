function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)));
}

async function subscribeToPush() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        console.warn('Push notification tidak didukung browser ini.');
        return;
    }

    const permission = await Notification.requestPermission();
    if (permission !== 'granted') {
        console.warn('Izin notifikasi ditolak.');
        return;
    }

    const registration = await navigator.serviceWorker.register('/sw.js');
    await navigator.serviceWorker.ready;

    const vapidPublicKey = document.querySelector('meta[name="vapid-public-key"]')?.content;
    if (!vapidPublicKey) {
        console.error('VAPID public key tidak ditemukan di halaman.');
        return;
    }

    let subscription = await registration.pushManager.getSubscription();

    if (!subscription) {
        subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
        });
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    await fetch('/push/subscribe', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(subscription.toJSON()),
    });
}

window.subscribeToPush = subscribeToPush;