{{-- Step Bar Partial --}}
<div class="h-14 bg-gradient-to-b from-[var(--bg-secondary)] to-[var(--bg-primary)] border-b border-[var(--border)] flex items-center justify-between px-6">
    {{-- Segmented Pill Navigation --}}
    <div class="relative flex items-center gap-1 bg-[var(--bg-tertiary)] rounded-full p-1">
        {{-- Sliding highlight background --}}
        <div 
            class="absolute h-[calc(100%-8px)] bg-[#E94B8C] rounded-full transition-all duration-300 ease-out"
            :style="{
                width: 'calc(25% - 4px)',
                left: currentStep === 'company' ? 'calc(0% + 4px)' : 
                      currentStep === 'goals' ? 'calc(25% + 4px)' : 
                      currentStep === 'todos' ? 'calc(50% + 4px)' : 'calc(75% + 4px)'
            }"
        ></div>

        {{-- Company Step --}}
        <button 
            @click="setStep('company')"
            :class="currentStep === 'company' ? 'text-white' : 'text-[var(--text-secondary)] hover:text-[var(--text-primary)]'"
            class="relative z-10 flex items-center gap-2 px-5 py-1.5 rounded-full text-sm transition-colors"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span>Company</span>
        </button>

        {{-- Goals Step --}}
        <button 
            @click="setStep('goals')"
            :class="currentStep === 'goals' ? 'text-white' : 'text-[var(--text-secondary)] hover:text-[var(--text-primary)]'"
            class="relative z-10 flex items-center gap-2 px-5 py-1.5 rounded-full text-sm transition-colors"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke-width="2"/>
                <circle cx="12" cy="12" r="6" stroke-width="2"/>
                <circle cx="12" cy="12" r="2" stroke-width="2"/>
            </svg>
            <span>Goals</span>
        </button>

        {{-- To-Dos Step --}}
        <button 
            @click="setStep('todos')"
            :class="currentStep === 'todos' ? 'text-white' : 'text-[var(--text-secondary)] hover:text-[var(--text-primary)]'"
            class="relative z-10 flex items-center gap-2 px-5 py-1.5 rounded-full text-sm transition-colors"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            <span>To-Dos</span>
        </button>

        {{-- Analysis Step --}}
        <button 
            @click="setStep('analysis')"
            :class="currentStep === 'analysis' ? 'text-white' : 'text-[var(--text-secondary)] hover:text-[var(--text-primary)]'"
            class="relative z-10 flex items-center gap-2 px-5 py-1.5 rounded-full text-sm transition-colors"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span>Analysis</span>
        </button>
    </div>

    {{-- Right Side: Theme Toggle + Profile Dropdown --}}
    <div class="flex items-center gap-4">
        {{-- Theme Toggle --}}
        <button 
            @click="darkMode = !darkMode"
            class="p-2 text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors"
        >
            <template x-if="darkMode">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </template>
            <template x-if="!darkMode">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </template>
        </button>

        {{-- Profile Dropdown --}}
        @include('axia.partials.profile-dropdown')
    </div>
</div>
