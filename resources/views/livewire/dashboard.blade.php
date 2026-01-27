<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Run;
use App\Models\Company;
use App\Services\WebhookAiService;

new
    #[Layout('components.layouts.app')]
    #[Title('Dashboard')]
    class extends Component {
    public ?Run $run = null;
    public ?Company $company = null;
    public array $goals = [];
    public array $todos = [];
    public array $evaluations = [];
    public int $focusScore = 0;
    public string $timeframe = '';
    public array $expandedTasks = [];
    public bool $isLoading = true;
    public bool $analysisComplete = false;
    public bool $needsOnboarding = false;

    public function mount(): void
    {
        $this->company = auth()->user()->company;

        // Check if user needs onboarding
        if (!$this->company) {
            $this->needsOnboarding = true;
            $this->isLoading = false;
            return;
        }

        $this->timeframe = $this->company->timeframe ?? 'This week';

        // Load goals
        $this->goals = $this->company->goals()->get()->map(fn($g) => [
            'id' => $g->id,
            'title' => $g->title,
            'priority' => $g->priority ?? 'mid',
        ])->toArray();

        // Check if user has goals
        if (count($this->goals) === 0) {
            $this->needsOnboarding = true;
            $this->isLoading = false;
            return;
        }

        // Load latest run
        $this->run = $this->company->runs()->latest()->first();

        if ($this->run) {
            $this->loadRunData();
        } else {
            $this->isLoading = false;
        }
    }

    public function loadRunData(): void
    {
        if (!$this->run)
            return;

        // Load todos
        $this->todos = $this->run->todos()->get()->map(fn($t) => [
            'id' => $t->id,
            'text' => $t->title,
        ])->toArray();

        // Check if analysis is complete
        $evaluationsCount = $this->run->evaluations()->count();

        if ($evaluationsCount > 0) {
            $this->analysisComplete = true;
            $this->loadEvaluations();
        }

        $this->isLoading = false;
    }

    public function loadEvaluations(): void
    {
        if (!$this->run)
            return;

        $evals = $this->run->evaluations()->with('todo')->get();

        $this->evaluations = $evals->map(fn($e) => [
            'id' => $e->id,
            'todoId' => $e->todo_id,
            'title' => $e->todo?->title ?? 'Unknown',
            'impact' => $this->getImpactLevel($e->score ?? 0),
            'score' => $e->score ?? 0,
            'summary' => $e->summary ?? '',
            'reasoning' => $e->reasoning ?? [],
            'relatedGoal' => $e->related_goal ?? 'General',
            'impactRating' => $e->impact_rating ?? '',
            'delegationFit' => $e->delegation_fit ?? '',
        ])->toArray();

        // Calculate focus score
        if (count($this->evaluations) > 0) {
            $totalScore = array_sum(array_column($this->evaluations, 'score'));
            $this->focusScore = (int) round($totalScore / count($this->evaluations));
        }
    }

    private function getImpactLevel(int $score): string
    {
        if ($score >= 70)
            return 'high';
        if ($score >= 40)
            return 'mid';
        return 'low';
    }

    public function toggleTask(string $id): void
    {
        if (in_array($id, $this->expandedTasks)) {
            $this->expandedTasks = array_filter($this->expandedTasks, fn($t) => $t !== $id);
        } else {
            $this->expandedTasks[] = $id;
        }
    }

    public function getScoreColor(): string
    {
        if ($this->focusScore >= 70)
            return 'var(--color-axia-green)';
        if ($this->focusScore >= 50)
            return 'var(--color-axia-yellow)';
        return 'var(--color-axia-orange)';
    }

    public function getImpactColor(string $impact): string
    {
        return match ($impact) {
            'high' => 'var(--color-axia-green)',
            'mid' => 'var(--color-axia-yellow)',
            default => 'var(--color-axia-orange)',
        };
    }

    public function getHighImpactGoals(): array
    {
        return array_filter($this->goals, fn($g) => $g['priority'] === 'high');
    }

    public function getTasksByImpact(string $impact): array
    {
        return array_filter($this->evaluations, fn($e) => $e['impact'] === $impact);
    }

    public function startNewAnalysis(): void
    {
        $this->redirect(route('app.todos'), navigate: true);
    }

    public function startOnboarding(): void
    {
        $this->redirect(route('app.company'), navigate: true);
    }
}; ?>

<div class="max-w-[1400px] mx-auto px-8 py-12">
    @if($isLoading)
        <!-- Loading State -->
        <div class="flex flex-col items-center justify-center py-20">
            <div
                class="w-16 h-16 border-4 border-[var(--border-color)] border-t-[var(--accent-pink)] rounded-full animate-spin mb-4">
            </div>
            <p class="text-[var(--text-secondary)]">Loading your dashboard...</p>
        </div>

    @elseif($needsOnboarding)
        <!-- Onboarding CTA -->
        <div class="flex flex-col items-center justify-center py-20">
            <div class="text-center max-w-lg">
                <div
                    class="w-20 h-20 mx-auto mb-6 rounded-full bg-[var(--accent-pink)]/10 flex items-center justify-center">
                    <flux:icon.sparkles class="w-10 h-10 text-[var(--accent-pink)]" />
                </div>
                <flux:heading size="xl" class="mb-4">Welcome to Axia</flux:heading>
                <p class="text-[var(--text-secondary)] mb-8">
                    Let's set up your company profile and goals so Axia can help you focus on what matters most.
                </p>
                <flux:button wire:click="startOnboarding" variant="primary">
                    Get Started
                </flux:button>
            </div>
        </div>

    @elseif(!$analysisComplete)
        <!-- No Analysis Yet -->
        <div class="flex flex-col items-center justify-center py-20">
            <div class="text-center max-w-lg">
                <div
                    class="w-20 h-20 mx-auto mb-6 rounded-full bg-[var(--accent-pink)]/10 flex items-center justify-center">
                    <flux:icon.clipboard-document-list class="w-10 h-10 text-[var(--accent-pink)]" />
                </div>
                <flux:heading size="xl" class="mb-4">Ready to Analyze</flux:heading>
                <p class="text-[var(--text-secondary)] mb-8">
                    Add your to-dos and let Axia analyze which tasks will have the highest impact on your goals.
                </p>
                <flux:button wire:click="startNewAnalysis" variant="primary">
                    Add To-Dos
                </flux:button>
            </div>
        </div>

    @else
        <!-- Dashboard Header -->
        <div class="flex items-center justify-between mb-8">
            <flux:heading size="xl">Your Focus Dashboard</flux:heading>
            <flux:button wire:click="startNewAnalysis" variant="primary">
                <flux:icon.plus class="w-4 h-4 mr-2" />
                New Analysis
            </flux:button>
        </div>

        <!-- TOP COMPONENT - 3 Columns -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <!-- Left: Company Info -->
            <div class="bg-[var(--bg-secondary)] rounded-2xl p-6 border border-[var(--border-color)]">
                <div class="text-xs text-[var(--text-secondary)] mb-4 uppercase tracking-wide">Company Info</div>
                <div class="space-y-3">
                    <div>
                        <div class="text-xs text-[var(--text-secondary)] mb-1">Name</div>
                        <div class="text-sm text-[var(--text-primary)]">{{ $company->name ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-[var(--text-secondary)] mb-1">Model</div>
                        <div class="text-sm text-[var(--text-primary)]">{{ $company->business_model ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-[var(--text-secondary)] mb-1">Team Size</div>
                        <div class="text-sm text-[var(--text-primary)]">{{ $company->team_size ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <!-- Center: Focus Score -->
            <div class="flex flex-col items-center justify-center">
                <div class="w-40 h-40 rounded-full flex items-center justify-center mb-4"
                    style="border: 6px solid color-mix(in srgb, {{ $this->getScoreColor() }} 30%, transparent); background-color: color-mix(in srgb, {{ $this->getScoreColor() }} 5%, transparent);">
                    <div class="text-center">
                        <div class="text-5xl text-[var(--text-primary)] mb-1">{{ $focusScore }}</div>
                        <div class="text-xs text-[var(--text-secondary)]">/100</div>
                    </div>
                </div>
                <div class="text-sm text-[var(--text-secondary)]">Focus Score</div>
            </div>

            <!-- Right: High-Impact Goals -->
            <div class="bg-[var(--bg-secondary)] rounded-2xl p-6 border border-[var(--border-color)]">
                <div class="text-xs text-[var(--text-secondary)] mb-4 uppercase tracking-wide">High-Impact Goals</div>
                <div class="space-y-4">
                    @forelse($this->getHighImpactGoals() as $goal)
                        <div>
                            <div class="text-sm text-[var(--text-primary)] mb-2">{{ $goal['title'] }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-[var(--text-secondary)]">No high-priority goals set</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- SCORE SUMMARY SECTION -->
        <div class="bg-[var(--bg-secondary)] rounded-2xl p-8 border border-[var(--border-color)] mb-12">
            <h2 class="text-xl text-[var(--text-primary)] mb-4">Summary of Your Focus Score</h2>
            <div class="space-y-4 text-[var(--text-secondary)]">
                <p>
                    Your focus score of {{ $focusScore }} indicates
                    @if($focusScore >= 70)
                        a strong alignment between your to-do list and strategic goals. Your tasks are well-focused on
                        high-impact activities.
                    @elseif($focusScore >= 50)
                        a moderate level of alignment between your to-do list and strategic goals. Consider delegating or
                        postponing some lower-impact tasks.
                    @else
                        limited alignment between your to-do list and strategic goals. We recommend reviewing your
                        priorities and focusing on tasks that directly support your key objectives.
                    @endif
                </p>
            </div>
        </div>

        <!-- TASK ACCORDION SECTION -->
        <div class="mb-12">
            <h2 class="text-xl text-[var(--text-primary)] mb-8">Your Tasks by Impact</h2>

            @foreach(['high', 'mid', 'low'] as $impactLevel)
                @php $tasks = $this->getTasksByImpact($impactLevel); @endphp
                @if(count($tasks) > 0)
                    <div class="mb-10">
                        <div class="text-xs text-[var(--text-secondary)] mb-4 uppercase tracking-wide">
                            {{ ucfirst($impactLevel) }} Impact
                        </div>
                        <div class="space-y-3">
                            @foreach($tasks as $task)
                                <div class="bg-[var(--bg-secondary)] rounded-xl border border-[var(--border-color)] overflow-hidden">
                                    <!-- Accordion Header -->
                                    <button wire:click="toggleTask('{{ $task['id'] }}')"
                                        class="w-full flex items-center gap-4 p-5 hover:bg-[var(--bg-hover)] transition-colors">
                                        <div class="w-1 h-12 rounded-full"
                                            style="background-color: {{ $this->getImpactColor($task['impact']) }};"></div>
                                        <div class="flex-1 text-left">
                                            <div class="text-[var(--text-primary)] text-sm">{{ $task['title'] }}</div>
                                        </div>
                                        <span class="px-3 py-1 rounded-lg text-xs border"
                                            style="background-color: color-mix(in srgb, {{ $this->getImpactColor($task['impact']) }} 10%, transparent); color: {{ $this->getImpactColor($task['impact']) }}; border-color: color-mix(in srgb, {{ $this->getImpactColor($task['impact']) }} 30%, transparent);">
                                            {{ ucfirst($task['impact']) }}
                                        </span>
                                        <span
                                            class="px-3 py-1 rounded-lg text-xs bg-[var(--bg-tertiary)] text-[var(--text-secondary)] border border-[var(--border-color)]">
                                            Score: {{ $task['score'] }}
                                        </span>
                                        @if(in_array($task['id'], $expandedTasks))
                                            <flux:icon.chevron-up class="w-5 h-5 text-[var(--text-secondary)]" />
                                        @else
                                            <flux:icon.chevron-down class="w-5 h-5 text-[var(--text-secondary)]" />
                                        @endif
                                    </button>

                                    <!-- Accordion Content -->
                                    @if(in_array($task['id'], $expandedTasks))
                                        <div class="px-5 pb-8 pt-6 border-t border-[var(--border-color)]">
                                            <div class="pl-5 space-y-6">
                                                <!-- Summary -->
                                                <p class="text-sm text-[var(--text-secondary)] leading-relaxed">
                                                    {{ $task['summary'] }}
                                                </p>

                                                <!-- Bullet Insights -->
                                                @if(!empty($task['reasoning']))
                                                    <ul class="space-y-3">
                                                        @foreach($task['reasoning'] as $reason)
                                                            <li class="text-sm text-[var(--text-secondary)] flex items-start gap-3">
                                                                <span class="text-[var(--text-secondary)] mt-0.5">•</span>
                                                                <span class="flex-1">{{ $reason }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif

                                                <!-- Metadata Tags -->
                                                <div class="flex flex-wrap gap-2 pt-2">
                                                    <span
                                                        class="px-3 py-1.5 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border-color)]">
                                                        {{ $task['relatedGoal'] }}
                                                    </span>
                                                    <span
                                                        class="px-3 py-1.5 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border-color)]">
                                                        {{ $task['impactRating'] }}
                                                    </span>
                                                    <span
                                                        class="px-3 py-1.5 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border-color)]">
                                                        {{ $task['delegationFit'] }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <!-- HOW AXIA ANALYZED THIS -->
        <div class="bg-[var(--bg-secondary)] rounded-2xl p-8 border border-[var(--border-color)]">
            <h3 class="text-lg text-[var(--text-primary)] mb-4">How was this analyzed?</h3>
            <p class="text-[var(--text-secondary)] mb-6">
                Axia uses a weighted formula that compares your task list against your stated goals, company context,
                and timeframe. Tasks are scored based on their direct contribution to high-priority objectives,
                potential revenue impact, and urgency within your current period.
            </p>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs text-[var(--text-secondary)]">Context used:</span>
                <span
                    class="px-3 py-1 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border-color)]">
                    Company info
                </span>
                <span
                    class="px-3 py-1 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border-color)]">
                    Goals ({{ count($goals) }})
                </span>
                <span
                    class="px-3 py-1 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border-color)]">
                    Task list ({{ count($todos) }})
                </span>
                <span
                    class="px-3 py-1 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border-color)]">
                    {{ $timeframe }}
                </span>
            </div>
        </div>
    @endif
</div>