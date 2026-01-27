<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Goal;
use App\Models\Company;

new
    #[Layout('components.layouts.app')]
    #[Title('Goals')]
    class extends Component {
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
    <flux:button wire:click="addGoal" variant="ghost" icon="plus"
        class="w-full mb-6 py-4 border-2 border-dashed border-[var(--border-color)] hover:border-[var(--accent-pink)]/30 rounded-2xl">
        Add Goal
    </flux:button>

    <!-- Goals List -->
    <div class="space-y-6">
        @foreach($goals as $index => $goal)
            <div class="bg-[var(--bg-secondary)] rounded-2xl p-6 border border-[var(--border-color)] space-y-4">
                <!-- Title -->
                <div class="flex gap-2 items-start">
                    <div class="flex-1">
                        <flux:input wire:model="goals.{{ $index }}.title" placeholder="Describe your goal" />
                    </div>
                    <flux:button wire:click="deleteGoal({{ $index }})" variant="ghost" size="sm" icon="x-mark" />
                </div>

                <!-- Description -->
                <flux:textarea wire:model="goals.{{ $index }}.description" placeholder="Additional details (optional)"
                    rows="2" />

                <!-- Priority -->
                <div>
                    <flux:label class="mb-2">Priority</flux:label>
                    <div class="flex gap-3">
                        @foreach(['high', 'mid', 'low'] as $priority)
                                    <button type="button" wire:click="updatePriority({{ $index }}, '{{ $priority }}')"
                                        class="px-6 py-2 rounded-lg border transition-colors {{ $goal['priority'] === $priority
                            ? ($priority === 'high'
                                ? 'bg-[var(--color-axia-green)]/10 border-[var(--color-axia-green)]/50 text-[var(--color-axia-green)]'
                                : ($priority === 'mid'
                                    ? 'bg-[var(--color-axia-yellow)]/10 border-[var(--color-axia-yellow)]/50 text-[var(--color-axia-yellow)]'
                                    : 'bg-[var(--color-axia-orange)]/10 border-[var(--color-axia-orange)]/50 text-[var(--color-axia-orange)]'))
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
        <flux:button wire:click="addGoal" variant="ghost" icon="plus"
            class="w-full mt-6 py-4 border-2 border-dashed border-[var(--border-color)] hover:border-[var(--accent-pink)]/30 rounded-2xl">
            Add Goal
        </flux:button>
    @endif

    <!-- Next Button -->
    @if(count($goals) > 0)
        <div class="flex justify-end mt-8">
            <flux:button wire:click="save" variant="primary">
                Continue to To-Dos
            </flux:button>
        </div>
    @endif
</div>