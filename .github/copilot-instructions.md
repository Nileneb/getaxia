# Axia Codebase Instructions

## Project Overview

Axia is an **AI-powered focus coach for startup founders** built with Laravel 12, Livewire 3/Volt, and Flux UI. It analyzes todos against company goals/KPIs to help founders prioritize high-impact work (80/20 principle).

**Core Flow**: User inputs todos → AI analyzes vs. goals/KPIs → System generates scores, evaluations, and identifies missing todos → Stores results for dashboard visualization.

**Tech Stack**:

- Backend: Laravel 12, PHP 8.4
- Frontend: Livewire 3 (Volt syntax), Flux UI, TailwindCSS
- AI: Langdock API (OpenAI-compatible)
- Build: Vite, Node 22

---

## Architecture

### Domain Flow

```
User submits todos → Run created → WebhookAiService.analyzeTodos() →
Langdock API → TodoEvaluations + MissingTodos stored → Dashboard display
```

### Key Models & Relationships

| Model            | Relationships                                        | Notes                                                     |
| ---------------- | ---------------------------------------------------- | --------------------------------------------------------- |
| **Company**      | owns Goals, Runs, KPIs                               | `owner_user_id`                                           |
| **Goal**         | has many `GoalKpi`                                   | belongs to Company                                        |
| **Run**          | has `todos`, `evaluations`, `missingTodos`, `aiLogs` | Analysis session                                          |
| **Todo**         | belongs to Run, has one `TodoEvaluation`             |                                                           |
| **SystemPrompt** | versioned by type                                    | `todo_analysis`, `company_extraction`, `goals_extraction` |

All models use **UUIDs** (`HasUuids` trait) and most have **SoftDeletes**.

### AI Integration

- **Service**: `app/Services/WebhookAiService.php`
- **Config**: `config/services.php` → Langdock settings
- **Validation**: `app/Services/AiResponseValidator.php`
- **Prompts**: DB via `SystemPrompt`, seeded from `database/seeders/SystemPromptsSeeder.php`

```php
$service = new WebhookAiService($user);
$result = $service->analyzeTodos($run, $todos, $company);
```

---

## Development Commands

```bash
composer dev      # Full environment (server + queue + vite)
composer test     # Run Pest tests
composer setup    # Initial setup
./vendor/bin/pint # Code formatting
npm run dev       # Vite dev server
npm run build     # Production build
```

### Cache Management

```bash
php artisan view:clear      # Clear view cache
php artisan livewire:clear  # Clear Livewire cache
php artisan config:clear    # Clear config cache
```

---

## Frontend Architecture

### CRITICAL: NO REDUNDANT FILES POLICY

**ALWAYS extend existing components. NEVER create duplicates.**

### Component Structure

```
resources/views/
├── components/           # Reusable X-Components
│   ├── layouts/
│   │   ├── app.blade.php       # Main app layout
│   │   ├── auth.blade.php      # Auth layout
│   │   └── app/sidebar.blade.php
│   └── settings/layout.blade.php
├── livewire/             # Volt components ONLY
│   ├── dashboard/
│   ├── goals/
│   └── todos/
└── pages/                # Full page views
```

### Layout Usage

```blade
<!-- App layout -->
<x-layouts.app title="Page Title">
    <flux:main>
        <!-- Content -->
    </flux:main>
</x-layouts.app>

<!-- Auth layout -->
<x-layouts.auth>
    <!-- Auth form -->
</x-layouts.auth>
```

### Flux UI Components

Always use Flux UI - never raw HTML for UI elements:

```blade
<!-- Forms -->
<flux:input label="Email" type="email" wire:model="email" />
<flux:button variant="primary" type="submit">Submit</flux:button>

<!-- Display -->
<flux:heading size="xl">{{ $title }}</flux:heading>
<flux:card>{{ $slot }}</flux:card>
<flux:badge variant="green">Active</flux:badge>

<!-- Tables -->
<flux:table>
    <flux:columns><flux:column>Title</flux:column></flux:columns>
    <flux:rows>
        <flux:row><flux:cell>{{ $item->title }}</flux:cell></flux:row>
    </flux:rows>
</flux:table>
```

### Livewire Volt Syntax

```blade
<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Validate;

new class extends Component {
    #[Validate('required|min:3')]
    public string $title = '';

    public function save()
    {
        $this->validate();
        // Save logic
        $this->dispatch('todo-created');
    }
}; ?>

<div>
    <flux:input label="Title" wire:model.live="title" />
    <flux:button wire:click="save" wire:loading.attr="disabled">
        <span wire:loading.remove>Save</span>
        <span wire:loading>Saving...</span>
    </flux:button>
</div>
```

### Page Patterns

**Dashboard**:

```blade
<x-layouts.app title="Dashboard">
    <flux:main class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Dashboard</flux:heading>
            <flux:button variant="primary">Action</flux:button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <flux:card><!-- Content --></flux:card>
        </div>
    </flux:main>
</x-layouts.app>
```

**Forms**:

```blade
<form wire:submit="save" class="space-y-6">
    <flux:input label="Title" wire:model="title" required />
    <div class="flex gap-3">
        <flux:button type="submit" variant="primary">Save</flux:button>
        <flux:button variant="ghost" href="{{ route('index') }}">Cancel</flux:button>
    </div>
</form>
```

---

## API Structure

All routes require `auth` middleware. Key endpoints:

| Endpoint                 | Method   | Description                        |
| ------------------------ | -------- | ---------------------------------- |
| `/api/todos`             | POST     | Create todos + trigger AI analysis |
| `/api/todos/batch`       | POST     | Batch create without analysis      |
| `/api/runs/{run}`        | GET      | Get run with evaluations           |
| `/api/goals`             | GET/POST | Goal management                    |
| `/api/goals/{goal}/kpis` | GET/POST | KPI management                     |
| `/api/chat/start`        | POST     | Start chat session                 |
| `/api/chat/message`      | POST     | Send chat message                  |

---

## Testing Patterns

### Pest + Laravel

```php
// Mock Langdock API
beforeEach(function () {
    config([
        'services.langdock.api_key' => 'test-key',
        'services.langdock.base_url' => 'https://api.langdock.test/v1',
        'services.langdock.model' => 'gpt-4o',
    ]);

    SystemPrompt::create([
        'type' => 'todo_analysis',
        'is_active' => true,
        'system_message' => 'Test',
        'user_prompt_template' => '{{todos_list}}',
    ]);
});

Http::fake([
    'api.langdock.test/*' => Http::response([
        'choices' => [['message' => ['content' => json_encode($data)]]],
        'usage' => ['total_tokens' => 500],
    ]),
]);
```

### Livewire Component Tests

```php
use Livewire\Volt\Volt;

test('can create todo', function () {
    Volt::test('todos.create')
        ->actingAs(User::factory()->create())
        ->set('title', 'New Todo')
        ->call('save')
        ->assertDispatched('todo-created');
});
```

### Factory States

```php
Todo::factory()->forRun($run)->withOwner('John')->create();
Run::factory()->forCompany($company)->create();
```

---

## Code Conventions

### Service Pattern

```php
public function __construct(?\App\Models\User $user = null)
{
    $this->user = $user ?? auth()->user();
}
```

### Evaluation Colors

| Color    | Score Range | Meaning                        |
| -------- | ----------- | ------------------------------ |
| `green`  | 70-100      | High impact, founder should do |
| `yellow` | 40-69       | Medium impact, delegatable     |
| `orange` | 0-39        | Low impact, delegate or drop   |

### DO ✅

- Use Flux UI components consistently
- Extend existing X-Components
- Follow Volt syntax for Livewire
- Implement loading states (`wire:loading`)
- Use UUIDs for all models

### DON'T ❌

- Create duplicate components
- Mix UI frameworks
- Use inline styles (use TailwindCSS)
- Skip validation on AI responses
- Create models without `HasUuids`

---

## Environment Variables

```env
# Required for AI
LANGDOCK_API_KEY=
LANGDOCK_BASE_URL=https://api.langdock.com/api/v1
LANGDOCK_MODEL=gpt-5

# Flux UI (composer auth)
FLUX_USERNAME=
FLUX_LICENSE_KEY=
```

---

## CI/CD

- Tests on push/PR to `develop` and `main`
- Pint linter runs automatically
- PHP 8.4, Node 22 required
- Flux UI credentials in CI secrets
