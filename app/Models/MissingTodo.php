<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MissingTodo extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'run_id',
        'goal_id',
        'kpi_id',
        'title',
        'description',
        'category',
        'impact_score',
        'suggested_owner_role',
    ];

    protected function casts(): array
    {
        return [
            'impact_score' => 'integer',
        ];
    }

    /**
     * Get the run for the missing todo.
     */
    public function run(): BelongsTo
    {
        return $this->belongsTo(Run::class);
    }

    /**
     * Get the goal for the missing todo.
     */
    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    /**
     * Get the KPI for the missing todo.
     */
    public function kpi(): BelongsTo
    {
        return $this->belongsTo(GoalKpi::class);
    }
}


