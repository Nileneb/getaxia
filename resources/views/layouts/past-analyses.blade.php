{{-- Past Analyses Page --}}
<div class="max-w-[1200px] mx-auto px-8 py-12">
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

    <h2 class="text-[var(--text-primary)] mb-8">All Analyses</h2>

    {{-- Analyses Grid --}}
    <template x-if="pastAnalyses.length === 0">
        <div class="bg-[var(--bg-secondary)] rounded-2xl p-12 border border-[var(--border)] text-center">
            <div class="text-[var(--text-secondary)] mb-2">No past analyses yet</div>
            <div class="text-sm text-[var(--text-secondary)]">
                Complete your first analysis to see it here
            </div>
        </div>
    </template>

    <template x-if="pastAnalyses.length > 0">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="analysis in pastAnalyses" :key="analysis.id">
                <button
                    @click="setPage('main'); setStep('analysis')"
                    class="bg-[var(--bg-secondary)] rounded-2xl p-6 border border-[var(--border)] hover:bg-[var(--bg-hover)] transition-colors text-left"
                >
                    <div class="flex items-start gap-4 mb-4">
                        {{-- Score Circle --}}
                        <div
                            class="w-16 h-16 rounded-full flex items-center justify-center flex-shrink-0"
                            :style="{
                                border: '3px solid ' + getScoreColor(analysis.score) + '30',
                                backgroundColor: getScoreColor(analysis.score) + '05'
                            }"
                        >
                            <div class="text-center">
                                <div class="text-xl text-[var(--text-primary)]" x-text="analysis.score"></div>
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="text-sm text-[var(--text-primary)] mb-1">Focus Score</div>
                            <div class="text-xs text-[var(--text-secondary)]" x-text="analysis.date"></div>
                        </div>
                    </div>

                    {{-- Summary --}}
                    <div class="text-sm text-[var(--text-secondary)]">
                        <span x-text="analysis.tasksAnalyzed"></span> tasks analyzed · <span x-text="analysis.goals"></span> goals
                    </div>
                </button>
            </template>
        </div>
    </template>
</div>
