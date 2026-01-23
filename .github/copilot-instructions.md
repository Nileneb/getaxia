# AI Coding Agent Instructions

## Project Overview

Getaxia is a Laravel 12 + Livewire 3 SaaS platform that uses AI (via Langdock API) to analyze user todos against company goals and KPIs. The app enables companies to track strategic alignment and performance.

**Core Flow**: User inputs todos → AI analyzes vs. goals/KPIs → System generates scores, evaluations, and identifies missing todos → Stores results in database for dashboard visualization.

## Architecture Patterns

### Data Model: Company → Goals → KPIs → Runs → Todos

- **Company**: Represents a business (uuid primary key, owner_user_id for multi-tenancy)
- **Goal**: Strategic objective within a company (has many KPIs, soft-deleted)
- **GoalKpi**: Key performance indicators tied to goals (or standalone at company level)
- **Run**: A todo analysis batch (period_start/end, overall_score, summary_text)
- **Todo**: Individual action items within a run (raw_input, normalized_title, source field)
- Key relationships use UUIDs and foreign keys; see [app/Models](app/Models)

### Service Layer: AI Integration via Langdock

- **WebhookAiService** ([app/Services/WebhookAiService.php](app/Services/WebhookAiService.php)): Central AI service
    - Calls Langdock (OpenAI-compatible API) for todo analysis, company extraction, goals extraction
    - Expects config in `config/services.php`: `langdock.api_key`, `langdock.base_url`, `langdock.model`
    - Returns structured JSON; validates with **AiResponseValidator** ([app/Services/AiResponseValidator.php](app/Services/AiResponseValidator.php))
    - Logs all requests/responses to `AiLog` table for audit trail
    - Always wraps AI calls in try-catch; returns `['success' => false, 'error' => '...']` on failure

- **UserContextService** ([app/Services/UserContextService.php](app/Services/UserContextService.php)): Builds context for prompts
    - Caches user/company/goals/KPIs for 5 minutes to minimize DB queries
    - Used to hydrate system prompts with actual user data before sending to AI

### API Endpoints (All Authenticated)

- `GET/POST /api/goals` - CRUD goals with nested KPIs
- `GET/POST/PATCH/DELETE /api/runs` - Query and update todo runs
- `POST /api/todos`, `POST /api/todos/batch` - Create todos and trigger analysis
- `POST /api/chat/start`, `POST /api/chat/message` - Multi-turn conversations
- See [routes/api.php](routes/api.php) for full routing

### Database & Migrations

- PostgreSQL (production) with SQLite (testing)
- Migrations in [database/migrations](database/migrations); use `php artisan migrate` locally
- **Factories** auto-generate test data: [database/factories](database/factories)
- Key fields: UUIDs, timestamps, soft-deletes on Goal, Run, Todo for logical deletion

## Developer Workflows

### Local Setup

```bash
composer install
npm install
php artisan key:generate
php artisan migrate --seed
npm run dev          # Vite for CSS/JS
php artisan serve    # Run server (default port 8000)
```

### Testing (Pest Framework)

- **Run all tests**: `php artisan test`
- **Run specific suite**: `php artisan test tests/Feature/LangdockServiceTest.php`
- **With coverage**: `php artisan test --coverage`
- Configuration: [phpunit.xml](phpunit.xml) uses in-memory SQLite, mocked HTTP
- **Pest syntax** (preferred over PHPUnit): `test('name', function () { ... })` and `beforeEach()`
- Mock external APIs with `Http::fake()` before making requests ([tests/Feature/LangdockServiceTest.php](tests/Feature/LangdockServiceTest.php#L14-L30) example)

### Building & Deployment

- **Build frontend**: `npm run build` (Vite + TailwindCSS)
- **Docker**: Runs with Nginx + PHP-FPM + PostgreSQL; compose file in [docker-compose.yaml](docker-compose.yaml)
    - Queue worker runs `php artisan queue:work` for async AI jobs
    - Storage volume at `/var/www/storage` for uploads/cache

### Key Commands

- `php artisan tinker` - Interactive shell for data inspection/debugging
- `php artisan pint` - Auto-format PHP code (PSR-12 standard)
- `php artisan pest` or `php artisan test` - Run Pest tests
- `php artisan queue:work` - Process background jobs (AI analysis tasks)

## Project-Specific Conventions

### Naming & Structure

- **Models**: Singular, PascalCase (Company, Goal, Todo) in `app/Models/`
- **Controllers**: Named by resource (TodoController, GoalController) in `app/Http/Controllers/Api/`
- **Services**: Descriptive (WebhookAiService, UserContextService) in `app/Services/`
- **Database**: snake_case columns; timestamps auto-added (created_at, updated_at)
- **Validation**: Inline with `Validator::make()` in controllers; custom rules in `app/Rules/` if complex

### Error Handling & Logging

- AI service errors logged to `AiLog` table with full request/response context; never silently fail
- HTTP errors: Return JSON responses with `message` and `errors` keys (see [app/Http/Controllers/Api/TodoController.php](app/Http/Controllers/Api/TodoController.php#L24-L30))
- Use `Illuminate\Support\Facades\Log` for application logs (e.g., `Log::info()`, `Log::error()`)

### AI Prompt Management

- **SystemPrompt model** stores versioned system & user prompt templates
- Active prompts fetched at runtime: `SystemPrompt::getActiveForType('todo_analysis')`
- Templates use `{{variable_name}}` placeholders replaced by `buildPromptFromTemplate()`
- Responses validated before storing via `AiResponseValidator` methods

### Response Structure & Validation

- Todo analysis response must include: `overall_score` (0-100), `evaluations` array, `missing_todos` list
- Each evaluation: `task_index`, `score` (0-100), `color` (green/yellow/orange), `reasoning`
- Company extraction allowed models: `b2b_saas`, `b2c`, `marketplace`, `agency`, `other`
- Validation throws `ValidationException` if structure invalid

### Testing Patterns

- **Mock HTTP**: `Http::fake([...])` before service calls; assert with `Http::assertSent()`
- **Database**: Factories create test data; use `Database::seed()` for specific seeders
- **Transactions**: Tests wrap in `DB::transaction()` to isolate changes
- **Auth**: Use `actingAs(User::factory()->create())` to simulate authenticated users
- Example: [tests/Feature/LangdockServiceTest.php](tests/Feature/LangdockServiceTest.php#L35-L65)

## Common Modification Points

### Adding a New Goal Analysis Feature

1. Create migration in [database/migrations](database/migrations) (e.g., `add_analysis_field_to_goals.php`)
2. Update **Goal model** [app/Models/Goal.php](app/Models/Goal.php) with new fillable/casts
3. Add service method in [app/Services/WebhookAiService.php](app/Services/WebhookAiService.php)
4. Create **AiResponseValidator** method for the new response type
5. Add API endpoint in [routes/api.php](routes/api.php) → Create controller method
6. Test with Pest: mock HTTP response, verify database state

### Fixing an AI Response Issue

1. Check `AiLog` table for failed request context (prompts, raw response)
2. Validate response JSON in `AiResponseValidator`; add specific error messages
3. Review `SystemPrompt` template—may need rewording for LLM clarity
4. Use Tinker to manually test: `WebhookAiService::new()->analyzeTodos(...)`
5. Update tests to catch regression

### Integrating a New External API

1. Add config entry in [config/services.php](config/services.php)
2. Create service class in [app/Services/](app/Services/) following WebhookAiService pattern
3. Log requests/responses to database table (e.g., AiLog) for debugging
4. Mock API in tests before calling service
5. Document error codes & retry logic

## File Reference Guide

- **Config**: [config/app.php](config/app.php), [config/services.php](config/services.php)
- **Database**: [database/migrations/](database/migrations/), [database/factories/](database/factories/)
- **Models**: [app/Models/](app/Models/) (Company, Goal, Run, Todo, AiLog, SystemPrompt, etc.)
- **Services**: [app/Services/](app/Services/) (WebhookAiService, AiResponseValidator, UserContextService)
- **Controllers**: [app/Http/Controllers/Api/](app/Http/Controllers/Api/) (TodoController, GoalController, RunController)
- **Routes**: [routes/api.php](routes/api.php)
- **Tests**: [tests/Feature/](tests/Feature/), [tests/Unit/](tests/Unit/) (Pest syntax)

## Debugging Tips

- **Environment**: Check `.env` for `LANGDOCK_API_KEY`, `LANGDOCK_BASE_URL`, `DB_CONNECTION`
- **Database**: Use `php artisan migrate:fresh --seed` to reset and repopulate test data
- **API Calls**: Inspect `AiLog` table for full request/response bodies
- **Pest Tests**: Run with `-v` flag for detailed output; use `dd()` to dump variables
- **Livewire (if applicable)**: Check `app/Livewire/` for component-specific logic
