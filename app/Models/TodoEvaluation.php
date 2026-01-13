<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TodoEvaluation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'run_id',
        'todo_id',
        'color',
        'score',
        'reasoning',
        'priority_recommendation',
        'action_recommendation',
        'delegation_target_role',
        'primary_goal_id',
        'primary_kpi_id',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
        ];
    }

    /**
     * Get the run for the evaluation.
     */
    public function run(): BelongsTo
    {
        return $this->belongsTo(Run::class);
    }

    /**
     * Get the todo for the evaluation.
     */
    public function todo(): BelongsTo
    {
        return $this->belongsTo(Todo::class);
    }

    /**
     * Get the primary goal for the evaluation.
     */
    public function primaryGoal(): BelongsTo
    {
        return $this->belongsTo(Goal::class, 'primary_goal_id');
    }

    /**
     * Get the primary KPI for the evaluation.
     */
    public function primaryKpi(): BelongsTo
    {
        return $this->belongsTo(GoalKpi::class, 'primary_kpi_id');
    }
}


