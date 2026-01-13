<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoalKpi extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'goal_id',
        'company_id',
        'name',
        'current_value',
        'target_value',
        'unit',
        'time_frame',
        'is_top_kpi',
        'original_smart_text',
        'extracted_from_text',
        'additional_information',
    ];

    public function aiMetadata(): MorphMany
    {
        return $this->morphMany(AiExtractedMetadata::class, 'entity', 'entity_type', 'entity_id');
    }

    protected function casts(): array
    {
        return [
            'current_value' => 'decimal:2',
            'target_value' => 'decimal:2',
            'is_top_kpi' => 'boolean',
            'extracted_from_text' => 'boolean',
        ];
    }

    /**
     * Get the goal that owns the KPI.
     */
    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    /**
     * Get the company for standalone KPIs.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}


