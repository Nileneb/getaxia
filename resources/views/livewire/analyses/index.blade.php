<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Run;

new
#[Layout('components.layouts.app')]
#[Title('Past Analyses')]
class extends Component {
    public array $analyses = [];

    public function mount(): void
    {
        $company = auth()->user()->company;

        if ($company) {
            $runs = $company->runs()
                ->whereHas('evaluations')
                ->with(['todos', 'evaluations'])
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($runs as $run) {
                $evalScores = $run->evaluations->pluck('score')->filter();
                $avgScore = $evalScores->count() > 0
                    ? (int) round($evalScores->avg())
                    : 0;

                $this->analyses[] = [
                    'id' => $run->id,
                    'date' => $run->created_at->format('M d, Y'),
                    'score' => $avgScore,
                    'tasksAnalyzed' => $run->todos->count(),
                    'goals' => $company->goals()->count(),
                ];
            }
        }
    }

    public function getScoreColor(int $score): string
    {
        if ($score >= 70)
            return '#4CAF50';
        if ($score >= 50)
            return '#FFB74D';
        return '#FF8A65';
    }

    public function viewAnalysis(string $id): void
    {
        $this->redirect(route('app.analysis', ['run' => $id]), navigate: true);
    }
}; ?>

<div class="max-w-[1200px] mx-auto px-8 py-12">
        <!-- Back Button -->
        <a href="{{ route('dashboard') }}" wire:navigate
            class="flex items-center gap-2 text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors mb-8">
            <flux:icon.arrow-left class="w-4 h-4" />
            <span class="text-sm">Back</span>
        </a>

        <h2 class="text-xl text-[var(--text-primary)] mb-8">All Analyses</h2>

        <!-- Analyses Grid -->
        @if(count($analyses) === 0)
            <div class="bg-[var(--bg-secondary)] rounded-2xl p-12 border border-[var(--border-color)] text-center">
                <div class="text-[var(--text-secondary)] mb-2">No past analyses yet</div>
                <div class="text-sm text-[var(--text-secondary)] mb-6">
                    Complete your first analysis to see it here
                </div>
                <a href="{{ route('app.company') }}" wire:navigate
                    class="inline-block px-6 py-3 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-lg transition-colors">
                    Start New Analysis
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($analyses as $analysis)
                    <button wire:click="viewAnalysis('{{ $analysis['id'] }}')"
                        class="bg-[var(--bg-secondary)] rounded-2xl p-6 border border-[var(--border-color)] hover:bg-[var(--bg-hover)] transition-colors text-left">
                        <div class="flex items-start gap-4 mb-4">
                            <!-- Score Circle -->
                            <div class="w-16 h-16 rounded-full flex items-center justify-center flex-shrink-0"
                                style="border: 3px solid {{ $this->getScoreColor($analysis['score']) }}30; background-color: {{ $this->getScoreColor($analysis['score']) }}05;">
                                <div class="text-center">
                                    <div class="text-xl text-[var(--text-primary)]">{{ $analysis['score'] }}</div>
                                </div>
                            </div>

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <div class="text-sm text-[var(--text-primary)] mb-1">Focus Score</div>
                                <div class="text-xs text-[var(--text-secondary)]">{{ $analysis['date'] }}</div>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="text-sm text-[var(--text-secondary)]">
                            {{ $analysis['tasksAnalyzed'] }} tasks analyzed • {{ $analysis['goals'] }} goals
                        </div>
                    </button>
                @endforeach
            </div>
        @endif
</div>
