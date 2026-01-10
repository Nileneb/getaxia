{{-- Analysis Page --}}
<div class="max-w-[1400px] mx-auto px-8 py-12">
    {{-- TOP COMPONENT - 3 Columns --}}
    <div class="grid grid-cols-3 gap-8 mb-12">
        {{-- Left: Company Info --}}
        <div class="bg-[var(--bg-secondary)] rounded-2xl p-6 border border-[var(--border)]">
            <div class="text-xs text-[var(--text-secondary)] mb-4 uppercase tracking-wide">Company Info</div>
            <div class="space-y-3">
                <div>
                    <div class="text-xs text-[var(--text-secondary)] mb-1">Name</div>
                    <div class="text-sm text-[var(--text-primary)]" x-text="companyData.name"></div>
                </div>
                <div>
                    <div class="text-xs text-[var(--text-secondary)] mb-1">Model</div>
                    <div class="text-sm text-[var(--text-primary)]" x-text="companyData.businessModel"></div>
                </div>
                <div>
                    <div class="text-xs text-[var(--text-secondary)] mb-1">Team Size</div>
                    <div class="text-sm text-[var(--text-primary)]" x-text="companyData.teamSize"></div>
                </div>
            </div>
        </div>

        {{-- Center: Focus Score --}}
        <div class="flex flex-col items-center justify-center">
            <div 
                class="w-40 h-40 rounded-full flex items-center justify-center mb-4"
                :style="{
                    border: '6px solid ' + getScoreColor(focusScore) + '30',
                    backgroundColor: getScoreColor(focusScore) + '05'
                }"
            >
                <div class="text-center">
                    <div class="text-5xl text-[var(--text-primary)] mb-1" x-text="focusScore"></div>
                    <div class="text-xs text-[var(--text-secondary)]">/100</div>
                </div>
            </div>
            <div class="text-sm text-[var(--text-secondary)]">Focus Score</div>
        </div>

        {{-- Right: High-Impact Goals --}}
        <div class="bg-[var(--bg-secondary)] rounded-2xl p-6 border border-[var(--border)]">
            <div class="text-xs text-[var(--text-secondary)] mb-4 uppercase tracking-wide">High-Impact Goals</div>
            <div class="space-y-4">
                <template x-for="goal in highImpactGoals" :key="goal.id">
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-[#4CAF50] mt-1.5 flex-shrink-0"></div>
                        <div>
                            <div class="text-sm text-[var(--text-primary)]" x-text="goal.title"></div>
                            <template x-if="goal.description">
                                <div class="text-xs text-[var(--text-secondary)] mt-1" x-text="goal.description"></div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Analysis Summary Text --}}
    <div class="bg-[var(--bg-secondary)] rounded-2xl p-6 border border-[var(--border)] mb-8">
        <div class="text-sm text-[var(--text-secondary)] leading-relaxed">
            Based on <span class="text-[var(--text-primary)]" x-text="companyData.name"></span>'s 
            <span class="text-[var(--text-primary)]" x-text="companyData.businessModel"></span> model and current goals, 
            we've analyzed <span class="text-[var(--text-primary)]" x-text="todos.length"></span> tasks for 
            <span class="text-[var(--text-primary)]" x-text="companyData.timeframe.toLowerCase()"></span>. 
            Your focus score indicates room for prioritization improvement.
        </div>
    </div>

    {{-- TASK ANALYSIS SECTIONS --}}
    <div class="space-y-8">
        {{-- High Impact Tasks --}}
        <div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-3 h-3 rounded-full bg-[#4CAF50]"></div>
                <h2 class="text-[var(--text-primary)]">High Impact</h2>
                <span class="text-sm text-[var(--text-secondary)]" x-text="'(' + highTasks.length + ')'"></span>
            </div>
            
            <div class="space-y-3">
                <template x-for="task in highTasks" :key="task.id">
                    <div class="bg-[var(--bg-secondary)] rounded-2xl border border-[var(--border)] overflow-hidden">
                        {{-- Task Header --}}
                        <button
                            @click="toggleTaskExpand(task.id)"
                            class="w-full flex items-center justify-between p-5 text-left hover:bg-[var(--bg-hover)] transition-colors"
                        >
                            <div class="flex items-center gap-4">
                                <div 
                                    class="w-12 h-12 rounded-full flex items-center justify-center text-sm"
                                    :style="{
                                        border: '2px solid ' + getImpactColor(task.impact) + '50',
                                        backgroundColor: getImpactColor(task.impact) + '10',
                                        color: getImpactColor(task.impact)
                                    }"
                                    x-text="task.score"
                                ></div>
                                <div>
                                    <div class="text-[var(--text-primary)]" x-text="task.text"></div>
                                    <div class="text-xs text-[var(--text-secondary)] mt-1" x-text="task.summary"></div>
                                </div>
                            </div>
                            <svg 
                                class="w-5 h-5 text-[var(--text-secondary)] transition-transform"
                                :class="{ 'rotate-180': isTaskExpanded(task.id) }"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        {{-- Task Details (Expanded) --}}
                        <template x-if="isTaskExpanded(task.id)">
                            <div class="px-5 pb-5 border-t border-[var(--border)]">
                                <div class="pt-4 grid grid-cols-3 gap-6">
                                    {{-- Reasoning --}}
                                    <div class="col-span-2">
                                        <div class="text-xs text-[var(--text-secondary)] uppercase tracking-wide mb-3">Why this matters</div>
                                        <ul class="space-y-2">
                                            <template x-for="reason in task.reasoning" :key="reason">
                                                <li class="flex items-start gap-2 text-sm text-[var(--text-secondary)]">
                                                    <span class="text-[#4CAF50] mt-0.5">→</span>
                                                    <span x-text="reason"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>

                                    {{-- Meta Info --}}
                                    <div class="space-y-3">
                                        <div>
                                            <div class="text-xs text-[var(--text-secondary)]">Related Goal</div>
                                            <div class="text-sm text-[var(--text-primary)]" x-text="task.relatedGoal"></div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-[var(--text-secondary)]">Impact Rating</div>
                                            <div class="text-sm text-[var(--text-primary)]" x-text="task.impactRating"></div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-[var(--text-secondary)]">Delegation Fit</div>
                                            <div class="text-sm text-[var(--text-primary)]" x-text="task.delegationFit"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        {{-- Mid Impact Tasks --}}
        <div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-3 h-3 rounded-full bg-[#FFB74D]"></div>
                <h2 class="text-[var(--text-primary)]">Mid Impact</h2>
                <span class="text-sm text-[var(--text-secondary)]" x-text="'(' + midTasks.length + ')'"></span>
            </div>
            
            <div class="space-y-3">
                <template x-for="task in midTasks" :key="task.id">
                    <div class="bg-[var(--bg-secondary)] rounded-2xl border border-[var(--border)] overflow-hidden">
                        <button
                            @click="toggleTaskExpand(task.id)"
                            class="w-full flex items-center justify-between p-5 text-left hover:bg-[var(--bg-hover)] transition-colors"
                        >
                            <div class="flex items-center gap-4">
                                <div 
                                    class="w-12 h-12 rounded-full flex items-center justify-center text-sm"
                                    :style="{
                                        border: '2px solid ' + getImpactColor(task.impact) + '50',
                                        backgroundColor: getImpactColor(task.impact) + '10',
                                        color: getImpactColor(task.impact)
                                    }"
                                    x-text="task.score"
                                ></div>
                                <div>
                                    <div class="text-[var(--text-primary)]" x-text="task.text"></div>
                                    <div class="text-xs text-[var(--text-secondary)] mt-1" x-text="task.summary"></div>
                                </div>
                            </div>
                            <svg 
                                class="w-5 h-5 text-[var(--text-secondary)] transition-transform"
                                :class="{ 'rotate-180': isTaskExpanded(task.id) }"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <template x-if="isTaskExpanded(task.id)">
                            <div class="px-5 pb-5 border-t border-[var(--border)]">
                                <div class="pt-4 grid grid-cols-3 gap-6">
                                    <div class="col-span-2">
                                        <div class="text-xs text-[var(--text-secondary)] uppercase tracking-wide mb-3">Why this matters</div>
                                        <ul class="space-y-2">
                                            <template x-for="reason in task.reasoning" :key="reason">
                                                <li class="flex items-start gap-2 text-sm text-[var(--text-secondary)]">
                                                    <span class="text-[#FFB74D] mt-0.5">→</span>
                                                    <span x-text="reason"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                    <div class="space-y-3">
                                        <div>
                                            <div class="text-xs text-[var(--text-secondary)]">Related Goal</div>
                                            <div class="text-sm text-[var(--text-primary)]" x-text="task.relatedGoal"></div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-[var(--text-secondary)]">Impact Rating</div>
                                            <div class="text-sm text-[var(--text-primary)]" x-text="task.impactRating"></div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-[var(--text-secondary)]">Delegation Fit</div>
                                            <div class="text-sm text-[var(--text-primary)]" x-text="task.delegationFit"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        {{-- Low Impact Tasks --}}
        <div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-3 h-3 rounded-full bg-[#FF8A65]"></div>
                <h2 class="text-[var(--text-primary)]">Low Impact</h2>
                <span class="text-sm text-[var(--text-secondary)]" x-text="'(' + lowTasks.length + ')'"></span>
            </div>
            
            <div class="space-y-3">
                <template x-for="task in lowTasks" :key="task.id">
                    <div class="bg-[var(--bg-secondary)] rounded-2xl border border-[var(--border)] overflow-hidden">
                        <button
                            @click="toggleTaskExpand(task.id)"
                            class="w-full flex items-center justify-between p-5 text-left hover:bg-[var(--bg-hover)] transition-colors"
                        >
                            <div class="flex items-center gap-4">
                                <div 
                                    class="w-12 h-12 rounded-full flex items-center justify-center text-sm"
                                    :style="{
                                        border: '2px solid ' + getImpactColor(task.impact) + '50',
                                        backgroundColor: getImpactColor(task.impact) + '10',
                                        color: getImpactColor(task.impact)
                                    }"
                                    x-text="task.score"
                                ></div>
                                <div>
                                    <div class="text-[var(--text-primary)]" x-text="task.text"></div>
                                    <div class="text-xs text-[var(--text-secondary)] mt-1" x-text="task.summary"></div>
                                </div>
                            </div>
                            <svg 
                                class="w-5 h-5 text-[var(--text-secondary)] transition-transform"
                                :class="{ 'rotate-180': isTaskExpanded(task.id) }"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <template x-if="isTaskExpanded(task.id)">
                            <div class="px-5 pb-5 border-t border-[var(--border)]">
                                <div class="pt-4 grid grid-cols-3 gap-6">
                                    <div class="col-span-2">
                                        <div class="text-xs text-[var(--text-secondary)] uppercase tracking-wide mb-3">Why this matters</div>
                                        <ul class="space-y-2">
                                            <template x-for="reason in task.reasoning" :key="reason">
                                                <li class="flex items-start gap-2 text-sm text-[var(--text-secondary)]">
                                                    <span class="text-[#FF8A65] mt-0.5">→</span>
                                                    <span x-text="reason"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                    <div class="space-y-3">
                                        <div>
                                            <div class="text-xs text-[var(--text-secondary)]">Related Goal</div>
                                            <div class="text-sm text-[var(--text-primary)]" x-text="task.relatedGoal"></div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-[var(--text-secondary)]">Impact Rating</div>
                                            <div class="text-sm text-[var(--text-primary)]" x-text="task.impactRating"></div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-[var(--text-secondary)]">Delegation Fit</div>
                                            <div class="text-sm text-[var(--text-primary)]" x-text="task.delegationFit"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex justify-center gap-4 mt-12">
        <button
            @click="handleNewWeek()"
            class="px-8 py-3 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-lg transition-colors"
        >
            Start New Week
        </button>
        <button
            @click="saveData()"
            class="px-8 py-3 bg-[var(--bg-tertiary)] border border-[var(--border)] text-[var(--text-primary)] rounded-lg hover:bg-[var(--bg-hover)] transition-colors"
        >
            Save Analysis
        </button>
    </div>
</div>
