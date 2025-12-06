<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#121212">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <title>POS - {{ config('app.name', 'Mika') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        /* Estilos específicos para POS */
        .pos-container {
            height: 100dvh;
            overflow: hidden;
        }

        .pos-products {
            height: calc(100dvh - 4rem);
            overflow-y: auto;
        }

        .pos-cart {
            height: calc(100dvh - 4rem);
            display: flex;
            flex-direction: column;
        }

        .cart-items {
            flex: 1;
            overflow-y: auto;
        }

        .cart-footer {
            flex-shrink: 0;
        }

        /* Scrollbar personalizado */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #1a1a1a;
        }

        ::-webkit-scrollbar-thumb {
            background: #333;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #444;
        }

        /* Animaciones */
        .cart-item-enter {
            animation: slideIn 0.2s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="bg-mika-bg text-white antialiased">
    <div class="pos-container">
        {{ $slot }}
    </div>

    @livewireScripts

    <!-- Notificaciones -->
    <div
        x-data="{ notifications: [] }"
        x-on:notify.window="
            let id = Date.now();
            notifications.push({ id, type: $event.detail.type, message: $event.detail.message });
            setTimeout(() => notifications = notifications.filter(n => n.id !== id), 3000);
        "
        class="fixed top-4 right-4 z-50 space-y-2"
    >
        <template x-for="notification in notifications" :key="notification.id">
            <div
                x-show="true"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-8"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                :class="{
                    'bg-green-600': notification.type === 'success',
                    'bg-red-600': notification.type === 'error',
                    'bg-yellow-600': notification.type === 'warning',
                    'bg-blue-600': notification.type === 'info'
                }"
                class="px-4 py-3 rounded-lg shadow-lg text-white font-medium"
            >
                <span x-text="notification.message"></span>
            </div>
        </template>
    </div>

    <!-- Audio para sonidos -->
    <audio id="beep-sound" preload="auto">
        <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2teleZmKhHtxb3N9h4+TkYyEenFpYVxZWV1kanJ5foKDg4F9d3Fta2loa2xwdHh6e3t5dXFtamhmZWVmZ2lrbnBycnJwbmtpZ2VlZGVmZ2lrbnBxcnJxb21rammZmZmZmZmZ" type="audio/wav">
    </audio>

    <script>
        // Sonido de beep
        function playBeep() {
            const audio = document.getElementById('beep-sound');
            if (audio) {
                audio.currentTime = 0;
                audio.play().catch(() => {});
            }
        }

        // Escuchar evento de producto agregado
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('notify', (data) => {
                if (data.type === 'success') {
                    playBeep();
                }
            });
        });

        // Prevenir zoom en móviles
        document.addEventListener('touchstart', function(event) {
            if (event.touches.length > 1) {
                event.preventDefault();
            }
        }, { passive: false });

        // Doble tap para prevenir zoom
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function(event) {
            const now = Date.now();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
    </script>
</body>
</html>
