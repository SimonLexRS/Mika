<nav class="bg-mika-surface border-t border-mika-surface-light safe-area-bottom">
    <div class="flex justify-around items-center h-16">
        {{-- Chat --}}
        <a
            href="{{ route('chat') }}"
            class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('chat') ? 'text-mika-primary' : 'text-mika-text-secondary' }} hover:text-mika-primary transition-colors"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <span class="text-xs mt-1">Chat</span>
        </a>

        {{-- Scanner --}}
        <a
            href="{{ route('scanner') }}"
            class="flex flex-col items-center justify-center w-full h-full text-mika-text-secondary"
        >
            <div class="bg-mika-primary rounded-full p-3 -mt-6 shadow-lg hover:bg-mika-primary-light transition-colors">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <span class="text-xs mt-1">Escanear</span>
        </a>

        {{-- Profile --}}
        <a
            href="{{ route('profile') }}"
            class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('profile') ? 'text-mika-primary' : 'text-mika-text-secondary' }} hover:text-mika-primary transition-colors"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span class="text-xs mt-1">Perfil</span>
        </a>
    </div>
</nav>
