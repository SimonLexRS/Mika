<div class="flex flex-wrap gap-2">
    @foreach($options as $option)
        <button
            wire:click="selectOption('{{ $option }}')"
            class="px-4 py-2 bg-mika-surface-light text-mika-primary text-sm rounded-full border border-mika-primary hover:bg-mika-primary hover:text-white transition-colors"
        >
            {{ $option }}
        </button>
    @endforeach
</div>
