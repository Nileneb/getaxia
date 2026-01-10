{{-- Goals Page --}}
<div class="max-w-3xl mx-auto px-6 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-[var(--text-primary)] mb-2">Goals for this period</h1>
        <p>Define your key goals with clear priorities.</p>
    </div>

    {{-- Add Goal Button (Top) --}}
    <button
        @click="addGoal()"
        class="w-full mb-6 py-4 bg-[var(--bg-secondary)] hover:bg-[var(--bg-hover)] border-2 border-dashed border-[var(--border)] hover:border-[#E94B8C]/30 rounded-2xl text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors flex items-center justify-center gap-2"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Goal
    </button>

    {{-- Goals List --}}
    <div class="space-y-6">
        <template x-for="goal in goals" :key="goal.id">
            <div class="bg-[var(--bg-secondary)] rounded-2xl p-6 space-y-4">
                {{-- Title --}}
                <div class="relative">
                    <input
                        type="text"
                        :value="goal.title"
                        @input="updateGoal(goal.id, 'title', $event.target.value)"
                        placeholder="Describe your goal"
                        class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 pr-10 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C]/50"
                    />
                    <button
                        @click="deleteGoal(goal.id)"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Description --}}
                <textarea
                    :value="goal.description"
                    @input="updateGoal(goal.id, 'description', $event.target.value)"
                    placeholder="Additional details (optional)"
                    rows="2"
                    class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C]/50 resize-none"
                ></textarea>

                {{-- Priority --}}
                <div>
                    <label class="block text-sm text-[var(--text-secondary)] mb-2">Priority</label>
                    <div class="flex gap-3">
                        <template x-for="priority in ['high', 'mid', 'low']" :key="priority">
                            <button
                                @click="updateGoal(goal.id, 'priority', priority)"
                                :class="{
                                    'bg-[#4CAF50]/10 border-[#4CAF50]/50 text-[#4CAF50]': goal.priority === priority && priority === 'high',
                                    'bg-[#FFB74D]/10 border-[#FFB74D]/50 text-[#FFB74D]': goal.priority === priority && priority === 'mid',
                                    'bg-[#FF8A65]/10 border-[#FF8A65]/50 text-[#FF8A65]': goal.priority === priority && priority === 'low',
                                    'bg-[var(--bg-tertiary)] border-[var(--border)] text-[var(--text-secondary)] hover:border-[var(--border)]': goal.priority !== priority
                                }"
                                class="px-6 py-2 rounded-lg border transition-colors capitalize"
                                x-text="priority.charAt(0).toUpperCase() + priority.slice(1)"
                            ></button>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Add Goal Button (Bottom) --}}
    <template x-if="goals.length > 0">
        <button
            @click="addGoal()"
            class="w-full mt-6 py-4 bg-[var(--bg-secondary)] hover:bg-[var(--bg-hover)] border-2 border-dashed border-[var(--border)] hover:border-[#E94B8C]/30 rounded-2xl text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors flex items-center justify-center gap-2"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Goal
        </button>
    </template>

    {{-- Next Button --}}
    <template x-if="goals.length > 0">
        <div class="flex justify-end mt-8">
            <button
                @click="setStep('todos')"
                class="px-8 py-3 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-lg transition-colors"
            >
                Continue to To-Dos
            </button>
        </div>
    </template>
</div>
