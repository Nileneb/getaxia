<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Run extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'company_id',
        'user_id',
        'period_start',
        'period_end',
        'snapshot_top_kpi_id',
        'overall_score',
        'summary_text',
    ];

    public function kpiSnapshots(): HasMany
    {
        return $this->hasMany(KpiSnapshot::class);
    }

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'overall_score' => 'integer',
        ];
    }

    /**
     * Get the company for the run.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user for the run.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the snapshot top KPI for the run.
     */
    public function snapshotTopKpi(): BelongsTo
    {
        return $this->belongsTo(GoalKpi::class, 'snapshot_top_kpi_id');
    }

    /**
     * Get the todos for the run.
     */
    public function todos(): HasMany
    {
        return $this->hasMany(Todo::class);
    }

    /**
     * Get the evaluations for the run.
     */
    public function evaluations(): HasMany
    {
        return $this->hasMany(TodoEvaluation::class);
    }

    /**
     * Get the missing todos for the run.
     */
    public function missingTodos(): HasMany
    {
        return $this->hasMany(MissingTodo::class);
    }

    /**
     * Get the AI logs for the run.
     */
    public function aiLogs(): HasMany
    {
        return $this->hasMany(AiLog::class);
    }

    /**
     * Get the system prompt used for this run (from AI logs).
     */
    public function getSystemPromptAttribute(): ?SystemPrompt
    {
        $aiLog = $this->aiLogs()
            ->where('prompt_type', 'todo_analysis')
            ->where('success', true)
            ->first();

        return $aiLog?->systemPrompt;
    }
}


