@php
    $isUser = ($message->sender ?? $message['sender'] ?? 'bot') === 'user';
    $type = $message->type ?? $message['type'] ?? 'text';
    $content = $message->content ?? $message['content'] ?? '';
    $metaData = $message->meta_data ?? $message['meta_data'] ?? null;
    $createdAt = $message->created_at ?? $message['created_at'] ?? now();

    // Normalizar tipo si es enum
    if (is_object($type) && method_exists($type, 'value')) {
        $type = $type->value;
    }
@endphp

<div class="flex {{ $isUser ? 'justify-end' : 'justify-start' }} message-enter">
    @unless($isUser)
        <div class="w-8 h-8 rounded-full bg-mika-primary flex items-center justify-center flex-shrink-0 mr-2">
            <x-mika-avatar class="w-6 h-6" />
        </div>
    @endunless

    <div class="max-w-[80%]">
        {{-- Text Message --}}
        @if($type === 'text')
            <div class="{{ $isUser
                ? 'bg-mika-primary text-white rounded-2xl rounded-tr-none'
                : 'bg-mika-surface text-white rounded-2xl rounded-tl-none'
            }} px-4 py-3">
                <p class="text-sm leading-relaxed whitespace-pre-wrap">{!! nl2br(e($content)) !!}</p>
            </div>
        @endif

        {{-- Card Message --}}
        @if($type === 'card' && $metaData)
            <livewire:components.action-card :data="$metaData" :title="$content" />
        @endif

        {{-- Quick Replies --}}
        @if($type === 'quick_replies')
            <div class="bg-mika-surface text-white rounded-2xl rounded-tl-none px-4 py-3 mb-2">
                <p class="text-sm leading-relaxed whitespace-pre-wrap">{!! nl2br(e($content)) !!}</p>
            </div>
            @if($metaData && isset($metaData['options']))
                <livewire:components.quick-replies :options="$metaData['options']" />
            @endif
        @endif

        {{-- Timestamp --}}
        <p class="text-mika-text-secondary text-xs mt-1 {{ $isUser ? 'text-right' : 'text-left' }}">
            {{ \Carbon\Carbon::parse($createdAt)->format('H:i') }}
        </p>
    </div>
</div>
