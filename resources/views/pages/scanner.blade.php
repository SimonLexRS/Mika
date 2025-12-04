<x-layouts.app>
    <div class="flex flex-col items-center justify-center h-full px-4">
        <div class="w-20 h-20 rounded-full bg-mika-surface flex items-center justify-center mb-6">
            <svg class="w-10 h-10 text-mika-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </div>

        <h2 class="text-xl font-semibold text-white mb-2">Escanear Recibo</h2>
        <p class="text-mika-text-secondary text-center mb-8">
            Próximamente podrás escanear tus recibos y Mika los registrará automáticamente.
        </p>

        <a href="{{ route('chat') }}" class="btn-primary">
            Volver al chat
        </a>
    </div>
</x-layouts.app>
