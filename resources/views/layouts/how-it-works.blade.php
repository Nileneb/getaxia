{{-- How It Works Page --}}
<div class="max-w-[800px] mx-auto px-8 py-12">
    {{-- Back Button --}}
    <button
        @click="setPage('main')"
        class="flex items-center gap-2 text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors mb-8"
    >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span class="text-sm">Back</span>
    </button>

    {{-- Welcome Card --}}
    <div class="bg-[var(--bg-secondary)] rounded-2xl p-8 border border-[var(--border)] mb-6">
        <h2 class="text-[var(--text-primary)] mb-6">Welcome to Axia</h2>
        <p class="text-[var(--text-secondary)] mb-4">Your AI Focus Coach</p>
    </div>

    {{-- How it Works --}}
    <div class="bg-[var(--bg-secondary)] rounded-2xl p-8 border border-[var(--border)] mb-6">
        <h3 class="text-[var(--text-primary)] mb-6">Here's how it works:</h3>
        
        <div class="space-y-6">
            {{-- Step 1 --}}
            <div class="flex gap-4">
                <div class="w-8 h-8 rounded-full bg-[var(--bg-tertiary)] border border-[var(--border)] flex items-center justify-center flex-shrink-0">
                    <span class="text-sm text-[var(--text-primary)]">1</span>
                </div>
                <div>
                    <div class="text-[var(--text-primary)] mb-1">Add company info</div>
                    <div class="text-sm text-[var(--text-secondary)]">
                        Tell us about your company, stage, and team size
                    </div>
                </div>
            </div>

            {{-- Step 2 --}}
            <div class="flex gap-4">
                <div class="w-8 h-8 rounded-full bg-[var(--bg-tertiary)] border border-[var(--border)] flex items-center justify-center flex-shrink-0">
                    <span class="text-sm text-[var(--text-primary)]">2</span>
                </div>
                <div>
                    <div class="text-[var(--text-primary)] mb-1">Define your goals</div>
                    <div class="text-sm text-[var(--text-secondary)]">
                        Set your top priorities and what you want to achieve
                    </div>
                </div>
            </div>

            {{-- Step 3 --}}
            <div class="flex gap-4">
                <div class="w-8 h-8 rounded-full bg-[var(--bg-tertiary)] border border-[var(--border)] flex items-center justify-center flex-shrink-0">
                    <span class="text-sm text-[var(--text-primary)]">3</span>
                </div>
                <div>
                    <div class="text-[var(--text-primary)] mb-1">Add your current To-Dos</div>
                    <div class="text-sm text-[var(--text-secondary)]">
                        Paste or upload your task list from any tool
                    </div>
                </div>
            </div>

            {{-- Step 4 --}}
            <div class="flex gap-4">
                <div class="w-8 h-8 rounded-full bg-[var(--bg-tertiary)] border border-[var(--border)] flex items-center justify-center flex-shrink-0">
                    <span class="text-sm text-[var(--text-primary)]">4</span>
                </div>
                <div>
                    <div class="text-[var(--text-primary)] mb-1">Get your analysis</div>
                    <div class="text-sm text-[var(--text-secondary)]">
                        Axia analyzes everything and shows you what truly matters
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Button --}}
    <div class="flex gap-4">
        <button
            @click="setPage('main'); setStep('company')"
            class="px-6 py-3 bg-white text-[var(--bg-primary)] rounded-lg hover:bg-[#E8E8E8] transition-colors"
        >
            Start Setup
        </button>
    </div>
</div>
