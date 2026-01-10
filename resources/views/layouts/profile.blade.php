{{-- Profile Page --}}
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

    {{-- Profile Form --}}
    <form @submit.prevent="saveData(); setPage('main')" class="bg-[var(--bg-secondary)] rounded-2xl p-8 border border-[var(--border)]">
        <h2 class="text-[var(--text-primary)] mb-8">Profile</h2>

        <div class="space-y-6">
            {{-- Name --}}
            <div>
                <label class="text-xs text-[var(--text-secondary)] mb-2 uppercase tracking-wide block">Name</label>
                <input
                    type="text"
                    x-model="userProfile.name"
                    class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-2.5 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C] transition-colors"
                    placeholder="Your name"
                />
            </div>

            {{-- Email --}}
            <div>
                <label class="text-xs text-[var(--text-secondary)] mb-2 uppercase tracking-wide block">Email</label>
                <input
                    type="email"
                    x-model="userProfile.email"
                    class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-2.5 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C] transition-colors"
                    placeholder="your@email.com"
                />
            </div>

            {{-- Company --}}
            <div>
                <label class="text-xs text-[var(--text-secondary)] mb-2 uppercase tracking-wide block">Company</label>
                <input
                    type="text"
                    x-model="userProfile.company"
                    class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-2.5 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C] transition-colors"
                    placeholder="Company name"
                />
            </div>
        </div>

        {{-- Save Button --}}
        <div class="mt-8 flex justify-end gap-3">
            <button
                type="button"
                @click="setPage('main')"
                class="px-6 py-2.5 text-sm text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors"
            >
                Cancel
            </button>
            <button
                type="submit"
                class="px-6 py-2.5 bg-[#E94B8C] hover:bg-[#D43D7A] text-white text-sm rounded-lg transition-colors"
            >
                Save Changes
            </button>
        </div>
    </form>
</div>
