<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Run;
use App\Models\Todo;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('To-Dos')]
new class extends Component {
    use WithFileUploads;

    public array $todos = [];
    public string $bulkInput = '';
    public $csvFile = null;

    public function mount(): void
    {
        // Load existing todos from last run if any
        $company = auth()->user()->company;
        if ($company) {
            $lastRun = $company->runs()->latest()->first();
            if ($lastRun) {
                foreach ($lastRun->todos as $todo) {
                    $this->todos[] = [
                        'id' => $todo->id,
                        'text' => $todo->title,
                    ];
                }
            }
        }
    }

    public function addBulkTodos(): void
    {
        if (empty(trim($this->bulkInput)))
            return;

        $lines = array_filter(
            array_map('trim', explode("\n", $this->bulkInput)),
            fn($line) => !empty($line)
        );

        foreach ($lines as $line) {
            $this->todos[] = [
                'id' => uniqid(),
                'text' => $line,
            ];
        }

        $this->bulkInput = '';
    }

    public function uploadCsv(): void
    {
        if (!$this->csvFile)
            return;

        $content = file_get_contents($this->csvFile->getRealPath());
        $lines = array_filter(
            array_map('trim', preg_split('/[\r\n]+/', $content)),
            fn($line) => !empty($line)
        );

        foreach ($lines as $line) {
            $this->todos[] = [
                'id' => uniqid(),
                'text' => $line,
            ];
        }

        $this->csvFile = null;
    }

    public function deleteTodo(int $index): void
    {
        unset($this->todos[$index]);
        $this->todos = array_values($this->todos);
    }

    public function analyze(): void
    {
        $company = auth()->user()->company;

        if (!$company) {
            return;
        }

        // Create a new run
        $run = $company->runs()->create([
            'status' => 'pending',
        ]);

        // Create todos for this run
        foreach ($this->todos as $todoData) {
            if (!empty($todoData['text'])) {
                $run->todos()->create([
                    'title' => $todoData['text'],
                ]);
            }
        }

        // Redirect to analysis page with run ID
        $this->redirect(route('app.analysis', ['run' => $run->id]), navigate: true);
    }
}; ?>

<div class="max-w-3xl mx-auto px-6 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-medium text-[var(--text-primary)] mb-2">Your To-Dos</h1>
            <p class="text-[var(--text-secondary)]">Paste, type, or upload your current tasks.</p>
        </div>

        <!-- Main Card -->
        <div class="bg-[var(--bg-secondary)] rounded-2xl p-8 border border-[var(--border-color)] space-y-8">
            <!-- Bulk Input -->
            <div class="space-y-3">
                <label class="block text-sm text-[var(--text-primary)]">Paste To-Dos (one per line)</label>
                <textarea wire:model="bulkInput"
                    placeholder="Write blog post&#10;Update landing page&#10;Review analytics" rows="6"
                    class="w-full bg-[var(--bg-tertiary)] border border-[var(--border-color)] rounded-lg px-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C]/50 resize-none"></textarea>
                @if(!empty(trim($bulkInput)))
                    <button wire:click="addBulkTodos"
                        class="px-4 py-2 bg-[var(--bg-tertiary)] hover:bg-[var(--bg-hover)] border border-[var(--border-color)] rounded-lg text-[var(--text-primary)] transition-colors">
                        Add All
                    </button>
                @endif
            </div>

            <div class="h-px bg-[var(--border-color)]"></div>

            <!-- File Upload -->
            <div>
                <label
                    class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-[var(--bg-tertiary)] hover:bg-[var(--bg-hover)] border border-[var(--border-color)] rounded-lg text-[var(--text-primary)] transition-colors">
                    <flux:icon.arrow-up-tray class="w-4 h-4" />
                    Upload CSV
                    <input type="file" wire:model="csvFile" accept=".csv,.txt" class="hidden" />
                </label>
                @if($csvFile)
                    <button wire:click="uploadCsv"
                        class="ml-2 px-4 py-2 bg-[var(--bg-tertiary)] hover:bg-[var(--bg-hover)] border border-[var(--border-color)] rounded-lg text-[var(--text-primary)] transition-colors">
                        Import
                    </button>
                @endif
            </div>

            @if(count($todos) > 0)
                <div class="h-px bg-[var(--border-color)]"></div>

                <!-- To-Do List -->
                <div class="space-y-2">
                    <label class="block text-sm text-[var(--text-primary)] mb-3">Current To-Dos</label>
                    @foreach($todos as $index => $todo)
                        <div
                            class="flex items-center gap-3 p-3 bg-[var(--bg-tertiary)] hover:bg-[var(--bg-hover)] rounded-lg group transition-colors">
                            <input type="text" wire:model="todos.{{ $index }}.text" placeholder="Enter task"
                                class="flex-1 bg-transparent text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none" />
                            <button wire:click="deleteTodo({{ $index }})"
                                class="text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors opacity-0 group-hover:opacity-100">
                                <flux:icon.x-mark class="w-4 h-4" />
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Analyze Button -->
            @if(count(array_filter($todos, fn($t) => !empty(trim($t['text'] ?? '')))) > 0)
                <div class="flex justify-center pt-6">
                    <button wire:click="analyze" wire:loading.attr="disabled"
                        class="px-12 py-3 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-lg transition-colors disabled:opacity-50">
                        <span wire:loading.remove>Start Analysis</span>
                        <span wire:loading>Analyzing...</span>
                    </button>
                </div>
            @endif
        </div>
</div>
