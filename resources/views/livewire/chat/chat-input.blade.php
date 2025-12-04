<div class="bg-mika-surface border-t border-mika-surface-light px-4 py-3 safe-area-bottom">
    <form wire:submit="sendMessage" class="flex items-center space-x-3">
        {{-- Attachment Button (placeholder for future) --}}
        <button
            type="button"
            class="p-2 text-mika-text-secondary hover:text-mika-primary transition-colors"
            title="Adjuntar imagen"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </button>

        {{-- Input Field --}}
        <div class="flex-1 relative">
            <input
                type="text"
                wire:model="message"
                placeholder="Escribe un mensaje..."
                class="w-full bg-mika-bg text-white rounded-full px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-mika-primary placeholder-mika-text-secondary"
                autocomplete="off"
            >
        </div>

        {{-- Send Button --}}
        <button
            type="submit"
            class="p-3 bg-mika-primary hover:bg-mika-primary-light rounded-full transition-colors disabled:opacity-50"
            wire:loading.attr="disabled"
        >
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
        </button>
    </form>
</div>
