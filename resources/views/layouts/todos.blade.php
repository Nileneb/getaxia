{{-- To-Dos Page --}}
<div x-data="{ bulkInput: '' }" class="max-w-3xl mx-auto px-6 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-[var(--text-primary)] mb-2">Your To-Dos</h1>
        <p>Paste, type, or upload your current tasks.</p>
    </div>

    {{-- Main Card --}}
    <div class="bg-[var(--bg-secondary)] rounded-2xl p-8 space-y-8">
        {{-- Bulk Input --}}
        <div class="space-y-3">
            <label class="block text-xs text-[var(--text-secondary)] uppercase tracking-wide">Paste your tasks (one per line)</label>
            <textarea
                x-model="bulkInput"
                placeholder="- Task one&#10;- Task two&#10;- Task three"
                rows="6"
                class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C]/50 resize-none"
            ></textarea>
            <div class="flex gap-3">
                <button
                    @click="addBulkTodos(bulkInput); bulkInput = ''"
                    class="px-6 py-2.5 bg-[var(--bg-tertiary)] border border-[var(--border)] text-[var(--text-primary)] rounded-lg hover:bg-[var(--bg-hover)] transition-colors"
                >
                    Add Tasks
                </button>
                <label class="px-6 py-2.5 bg-[var(--bg-tertiary)] border border-[var(--border)] text-[var(--text-secondary)] rounded-lg hover:bg-[var(--bg-hover)] transition-colors cursor-pointer flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Upload file
                    <input type="file" accept=".txt,.csv" class="hidden" @change="handleFileUpload($event)" />
                </label>
            </div>
        </div>

        {{-- Divider --}}
        <div class="h-px bg-[var(--border)]"></div>

        {{-- Tasks List --}}
        <div class="space-y-3">
            <label class="block text-xs text-[var(--text-secondary)] uppercase tracking-wide">Your Tasks (<span x-text="todos.length"></span>)</label>
            
            {{-- Empty State --}}
            <template x-if="todos.length === 0">
                <div class="text-center py-8 text-[var(--text-secondary)]">
                    No tasks added yet. Paste or type your tasks above.
                </div>
            </template>

            {{-- Task Items --}}
            <div class="space-y-2">
                <template x-for="todo in todos" :key="todo.id">
                    <div class="flex items-center gap-3 group">
                        <input
                            type="text"
                            :value="todo.text"
                            @input="updateTodo(todo.id, $event.target.value)"
                            class="flex-1 bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-2.5 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C]/50"
                        />
                        <button
                            @click="deleteTodo(todo.id)"
                            class="p-2 text-[var(--text-secondary)] hover:text-[#FF8A65] transition-colors opacity-0 group-hover:opacity-100"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Analyze Button --}}
    <template x-if="todos.length > 0">
        <div class="flex justify-end mt-8">
            <button
                @click="isLoading = true; setTimeout(() => { isLoading = false; setStep('analysis'); }, 2000)"
                class="px-8 py-3 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-lg transition-colors flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Analyze Tasks
            </button>
        </div>
    </template>
</div>
