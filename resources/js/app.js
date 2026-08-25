import './bootstrap';
import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';

window.Alpine = Alpine;

// Toast store for lightweight notifications
Alpine.store('toast', {
    toasts: [],
    show(message, type = 'success', duration = 3500) {
        const id = Date.now() + Math.random().toString(36).substr(2, 9);
        this.toasts.push({ id, message, type });
        setTimeout(() => {
            this.remove(id);
        }, duration);
    },
    remove(id) {
        this.toasts = this.toasts.filter(t => t.id !== id);
    }
});

// Start Alpine
Alpine.start();

// Initialize Lucide icons
document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
});
