{{-- Chat Panel Partial --}}
<div class="w-[270px] h-screen bg-[var(--bg-secondary)] border-r border-[var(--border)] flex flex-col">
    {{-- Logo --}}
    <div class="p-6 border-b border-[var(--border)]">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#E94B8C] to-[#B03A6F] flex items-center justify-center">
                <span class="text-white text-sm">A</span>
            </div>
            <span class="text-[var(--text-primary)]">Axia</span>
        </div>
    </div>

    {{-- Messages --}}
    <div class="flex-1 overflow-y-auto p-4 space-y-3">
        <template x-for="message in messages" :key="message.id">
            <div 
                :class="message.sender === 'user' 
                    ? 'bg-[var(--bg-tertiary)] ml-4' 
                    : 'bg-[var(--bg-primary)] mr-4'"
                class="p-3 rounded-lg"
            >
                <p class="text-sm text-[var(--text-primary)]" x-text="message.text"></p>
            </div>
        </template>
    </div>

    {{-- Input --}}
    <form @submit.prevent="sendMessage($refs.chatInput.value); $refs.chatInput.value = ''" class="p-4 border-t border-[var(--border)]">
        <div class="relative">
            <input
                type="text"
                x-ref="chatInput"
                placeholder="Ask Axia..."
                class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-2.5 pr-10 text-sm text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C]/50"
            />
            <button
                type="submit"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors"
            >
                {{-- Send Icon --}}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </button>
        </div>
    </form>
</div>
