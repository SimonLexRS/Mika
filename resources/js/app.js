import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Inicializar Alpine.js
Alpine.start();

// Helper para scroll al fondo del chat
window.scrollToBottom = function(element) {
    if (element) {
        element.scrollTop = element.scrollHeight;
    }
};

// Detectar si es dispositivo móvil
window.isMobile = function() {
    return window.innerWidth < 768;
};

// Vibración haptic para feedback (si está disponible)
window.hapticFeedback = function(type = 'light') {
    if ('vibrate' in navigator) {
        switch(type) {
            case 'light':
                navigator.vibrate(10);
                break;
            case 'medium':
                navigator.vibrate(20);
                break;
            case 'heavy':
                navigator.vibrate([30, 10, 30]);
                break;
        }
    }
};
