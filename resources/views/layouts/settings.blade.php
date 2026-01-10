{{-- Settings Page --}}
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

    {{-- Settings Card --}}
    <div class="bg-[var(--bg-secondary)] rounded-2xl p-8 border border-[var(--border)]">
        <h2 class="text-[var(--text-primary)] mb-8">Profile Settings</h2>

        <div class="space-y-6" x-data="{ notifications: true }">
            {{-- Name --}}
            <div>
                <label class="text-xs text-[var(--text-secondary)] mb-2 uppercase tracking-wide block">Name</label>
                <input
                    type="text"
                    x-model="userProfile.name"
                    class="w-full px-4 py-3 bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#4A4D52]"
                />
            </div>

            {{-- Email --}}
            <div>
                <label class="text-xs text-[var(--text-secondary)] mb-2 uppercase tracking-wide block">Email</label>
                <input
                    type="email"
                    x-model="userProfile.email"
                    class="w-full px-4 py-3 bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#4A4D52]"
                />
            </div>

            {{-- Notifications --}}
            <div>
                <label class="text-xs text-[var(--text-secondary)] mb-2 uppercase tracking-wide block">Notifications</label>
                <div class="flex items-center gap-3">
                    <button
                        @click="notifications = !notifications"
                        :class="notifications ? 'bg-[#4CAF50]' : 'bg-[var(--bg-tertiary)]'"
                        class="w-12 h-6 rounded-full transition-colors border border-[var(--border)]"
                    >
                        <div
                            :class="notifications ? 'translate-x-7' : 'translate-x-1'"
                            class="w-4 h-4 bg-white rounded-full transition-transform"
                        ></div>
                    </button>
                    <span class="text-sm text-[var(--text-secondary)]" x-text="notifications ? 'Enabled' : 'Disabled'"></span>
                </div>
            </div>

            {{-- Save Button --}}
            <div class="pt-4">
                <button
                    @click="saveData(); setPage('main')"
                    class="px-6 py-3 bg-white text-[var(--bg-primary)] rounded-lg hover:bg-[#E8E8E8] transition-colors"
                >
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
