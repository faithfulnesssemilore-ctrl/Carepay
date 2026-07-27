// resources/js/notification-stream.js
let notificationStream = null;
let reconnectTimer = null;

export function startNotificationStream() {
    if (notificationStream || ! window.Livewire) {
        return;
    }

    notificationStream = new EventSource('/notifications/stream');

    notificationStream.onmessage = (event) => {
        if (! event.data) {
            return;
        }

        try {
            const data = JSON.parse(event.data);

            if (data.type === 'heartbeat') {
                return;
            }

            if (window.Livewire) {
                window.Livewire.dispatch('notification-received', data);
            }

            showNotificationToast(data.notifications?.[0]);
        } catch (error) {
            console.error('Notification stream payload error', error);
        }
    };

    notificationStream.onerror = () => {
        if (notificationStream) {
            notificationStream.close();
            notificationStream = null;
        }

        if (reconnectTimer) {
            clearTimeout(reconnectTimer);
        }

        reconnectTimer = window.setTimeout(() => {
            startNotificationStream();
        }, 5000);
    };
}

export function stopNotificationStream() {
    if (reconnectTimer) {
        clearTimeout(reconnectTimer);
        reconnectTimer = null;
    }

    if (notificationStream) {
        notificationStream.close();
        notificationStream = null;
    }
}

function showNotificationToast(notification) {
    if (! notification) {
        return;
    }

    const container = document.getElementById('toast-container');
    if (! container) {
        return;
    }

    const message = `${notification.message} · ${notification.created_at}`;
    const toast = document.createElement('div');
    toast.className = 'toast align-items-center text-white border-0 rounded-3 shadow-lg';
    toast.style.cssText = 'background: rgba(15, 23, 42, 0.95); border: 1px solid rgba(168, 85, 247, 0.35); padding: 0.85rem 1rem; margin-bottom: 0.75rem; max-width: 320px;';
    toast.innerHTML = `<div class="toast-body">${message}</div>`;
    container.appendChild(toast);

    window.setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.25s ease';
        window.setTimeout(() => toast.remove(), 250);
    }, 4000);
}

window.startNotificationStream = startNotificationStream;
window.stopNotificationStream = stopNotificationStream;