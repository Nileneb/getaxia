<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Goal;
use App\Models\Company;

#[Layout('components.layouts.app')]
#[Title('Goals')]
new class extends Component {
    public array $goals = [];

    public function mount(): void
    {
        $company = auth()->user()->company;

        if ($company) {
            $existingGoals = $company->goals()->get();
            foreach ($existingGoals as $goal) {
                $this->goals[] = [
                    'id' => $goal->id,
                    'title' => $goal->title,
                    'description' => $goal->description ?? '',
                    'priority' => $goal->priority ?? 'mid',
                ];
            }
        }
    }

    public function addGoal(): void
    {
        $this->goals[] = [
            'id' => uniqid(),
            'title' => '',
            'description' => '',
            'priority' => 'mid',
        ];
    }

    public function updatePriority(int $index, string $priority): void
    {
        if (isset($this->goals[$index])) {
            $this->goals[$index]['priority'] = $priority;
        }
    }

    public function deleteGoal(int $index): void
    {
        unset($this->goals[$index]);
        $this->goals = array_values($this->goals);
    }

    public function save(): void
    {
        $company = auth()->user()->company;

        if (!$company) {
            return;
        }

        // Delete existing goals
        $company->goals()->delete();

        // Create new goals
        foreach ($this->goals as $goalData) {
            if (!empty($goalData['title'])) {
                $company->goals()->create([
                    'title' => $goalData['title'],
                    'description' => $goalData['description'] ?? '',
                    'priority' => $goalData['priority'] ?? 'mid',
                ]);
            }
        }

        $this->redirect(route('app.todos'), navigate: true);
    }
}; ?>

<div class="max-w-3xl mx-auto px-6 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-medium text-[var(--text-primary)] mb-2">Goals for this period</h1>
            <p class="text-[var(--text-secondary)]">Define your key goals with clear priorities.</p>
        </div>

        <!-- Add Goal Button (Top) -->
        <button wire:click="addGoal"
            class="w-full mb-6 py-4 bg-[var(--bg-secondary)] hover:bg-[var(--bg-hover)] border-2 border-dashed border-[var(--border-color)] hover:border-[#E94B8C]/30 rounded-2xl text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors flex items-center justify-center gap-2">
            <flux:icon.plus class="w-5 h-5" />
            Add Goal
        </button>

        <!-- Goals List -->
        <div class="space-y-6">
            @foreach($goals as $index => $goal)
                <div class="bg-[var(--bg-secondary)] rounded-2xl p-6 border border-[var(--border-color)] space-y-4">
                    <!-- Title -->
                    <div class="relative">
                        <input type="text" wire:model="goals.{{ $index }}.title" placeholder="Describe your goal"
                            class="w-full bg-[var(--bg-tertiary)] border border-[var(--border-color)] rounded-lg px-4 py-3 pr-10 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C]/50" />
                        <button wire:click="deleteGoal({{ $index }})"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors">
                            <flux:icon.x-mark class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Description -->
                    <textarea wire:model="goals.{{ $index }}.description" placeholder="Additional details (optional)"
                        rows="2"
                        class="w-full bg-[var(--bg-tertiary)] border border-[var(--border-color)] rounded-lg px-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C]/50 resize-none"></textarea>

                    <!-- Priority -->
                    <div>
                        <label class="block text-sm text-[var(--text-secondary)] mb-2">Priority</label>
                        <div class="flex gap-3">
                            @foreach(['high', 'mid', 'low'] as $priority)
                                            <button type="button" wire:click="updatePriority({{ $index }}, '{{ $priority }}')"
                                                class="px-6 py-2 rounded-lg border transition-colors {{ $goal['priority'] === $priority
                                ? ($priority === 'high'
                                    ? 'bg-[#4CAF50]/10 border-[#4CAF50]/50 text-[#4CAF50]'
                                    : ($priority === 'mid'
                                        ? 'bg-[#FFB74D]/10 border-[#FFB74D]/50 text-[#FFB74D]'
                                        : 'bg-[#FF8A65]/10 border-[#FF8A65]/50 text-[#FF8A65]'))
                                : 'bg-[var(--bg-tertiary)] border-[var(--border-color)] text-[var(--text-secondary)] hover:border-[var(--border-color)]' }}">
                                                {{ ucfirst($priority) }}
                                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Add Goal Button (Bottom) -->
        @if(count($goals) > 0)
            <button wire:click="addGoal"
                class="w-full mt-6 py-4 bg-[var(--bg-secondary)] hover:bg-[var(--bg-hover)] border-2 border-dashed border-[var(--border-color)] hover:border-[#E94B8C]/30 rounded-2xl text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors flex items-center justify-center gap-2">
                <flux:icon.plus class="w-5 h-5" />
                Add Goal
            </button>
        @endif

        <!-- Next Button -->
        @if(count($goals) > 0)
            <div class="flex justify-end mt-8">
                <button wire:click="save" wire:loading.attr="disabled"
                    class="px-8 py-3 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-lg transition-colors disabled:opacity-50">
                    <span wire:loading.remove>Continue to To-Dos</span>
                    <span wire:loading>Saving...</span>
                </button>
            </div>
        @endif
</div>
