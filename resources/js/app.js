import './bootstrap';

import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';

window.Alpine = Alpine;

Alpine.start();

window.refreshLucideIcons = () => {
    createIcons({ icons });
};

document.addEventListener('DOMContentLoaded', () => {
    window.refreshLucideIcons();
});

document.addEventListener('livewire:navigated', () => {
    window.refreshLucideIcons();
});
