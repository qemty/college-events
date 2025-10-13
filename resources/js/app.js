import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
window.Chart = Chart;

window.Alpine = Alpine;
Alpine.start();

// Инициализация темы
document.addEventListener('DOMContentLoaded', () => {
    const theme = localStorage.getItem('theme');
    if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
});