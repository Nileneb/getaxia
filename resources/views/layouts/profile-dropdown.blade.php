{{-- Profile Dropdown Partial --}}
<div x-data="{ isOpen: false }" class="relative">
    {{-- Avatar Button --}}
    <button
        @click="isOpen = !isOpen"
        class="w-10 h-10 rounded-full bg-[var(--bg-tertiary)] border border-[var(--border)] flex items-center justify-center hover:bg-[var(--bg-hover)] transition-colors"
    >
        <svg class="w-5 h-5 text-[var(--text-secondary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
    </button>

    {{-- Dropdown Menu --}}
    <div 
        x-show="isOpen"
        @click.outside="isOpen = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-64 bg-[var(--bg-secondary)] border border-[var(--border)] rounded-xl shadow-2xl overflow-hidden z-50"
    >
        <div class="py-2">
            {{-- Profile --}}
            <button
                @click="setPage('profile'); isOpen = false"
                class="w-full flex items-center gap-3 px-4 py-3 text-left text-[var(--text-primary)] hover:bg-[var(--bg-hover)] transition-colors"
            >
                <svg class="w-5 h-5 text-[var(--text-secondary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="text-sm">Profile</span>
            </button>

            {{-- Past Analyses --}}
            <button
                @click="setPage('past-analyses'); isOpen = false"
                class="w-full flex items-center gap-3 px-4 py-3 text-left text-[var(--text-primary)] hover:bg-[var(--bg-hover)] transition-colors"
            >
                <svg class="w-5 h-5 text-[var(--text-secondary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span class="text-sm">Past Analyses</span>
            </button>

            {{-- How It Works --}}
            <button
                @click="setPage('how-it-works'); isOpen = false"
                class="w-full flex items-center gap-3 px-4 py-3 text-left text-[var(--text-primary)] hover:bg-[var(--bg-hover)] transition-colors"
            >
                <svg class="w-5 h-5 text-[var(--text-secondary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm">How It Works</span>
            </button>

            {{-- Divider --}}
            <div class="my-1 h-px bg-[var(--border)]"></div>

            {{-- Logout --}}
            <button
                @click="logout()"
                class="w-full flex items-center gap-3 px-4 py-3 text-left text-[#FF8A65] hover:bg-[var(--bg-hover)] transition-colors"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span class="text-sm">Log Out</span>
            </button>
        </div>
    </div>

    {{-- Hidden Logout Form --}}
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>
</div>
