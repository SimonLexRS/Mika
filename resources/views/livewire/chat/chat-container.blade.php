<div
    class="flex flex-col h-full bg-mika-bg"
    x-data="{
        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.messagesContainer;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        }
    }"
    x-init="scrollToBottom()"
    @scroll-to-bottom.window="scrollToBottom()"
>
    {{-- Header --}}
    <header class="flex items-center px-4 py-3 bg-mika-surface border-b border-mika-surface-light">
        <div class="w-10 h-10 rounded-full bg-mika-primary flex items-center justify-center">
            <x-mika-avatar class="w-8 h-8" />
        </div>
        <div class="ml-3">
            <h1 class="text-white font-semibold">Mika</h1>
            <p class="text-mika-text-secondary text-xs">Tu asistente financiero</p>
        </div>
    </header>

    {{-- Messages Container --}}
    <div
        x-ref="messagesContainer"
        class="flex-1 overflow-y-auto px-4 py-4 space-y-4 chat-scrollbar"
    >
        @foreach($messages as $message)
            <livewire:chat.message-bubble
                :message="$message"
                :key="is_array($message) ? ($message['id'] ?? uniqid()) : $message->id"
            />
        @endforeach

        {{-- Typing Indicator --}}
        @if($isTyping)
            <div class="flex items-start space-x-2 message-enter">
                <div class="w-8 h-8 rounded-full bg-mika-primary flex items-center justify-center flex-shrink-0">
                    <x-mika-avatar class="w-6 h-6" />
                </div>
                <div class="bg-mika-surface rounded-2xl rounded-tl-none px-4 py-3">
                    <div class="flex space-x-1">
                        <span class="w-2 h-2 bg-mika-text-secondary rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                        <span class="w-2 h-2 bg-mika-text-secondary rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                        <span class="w-2 h-2 bg-mika-text-secondary rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Input Area --}}
    <livewire:chat.chat-input />
</div>
