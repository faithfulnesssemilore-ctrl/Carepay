// resources/js/notification-stream.js
export function startNotificationStream() {
    const eventSource = new EventSource('/notifications/stream');

    eventSource.onmessage = (event) => {
        const data = JSON.parse(event.data);
        
        // Send to Livewire
        window.Livewire.dispatch('notification-received', data);
        
        // Optional: Show toast notification
        showNotificationToast(data.notifications[0]);
    };

    eventSource.onerror = () => {
        console.error('Notification stream error');
        eventSource.close();
        
        // Reconnect after 5 seconds
        setTimeout(startNotificationStream, 5000);
    };
}

function showNotificationToast(notification) {
    // Using your toast library (if you have one)
    const message = `${notification.message} - ${notification.created_at}`;
    console.log(' Notification:', message);
}