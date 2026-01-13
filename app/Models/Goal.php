<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Goal extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'company_id',
        'title',
        'description',
        'priority',
        'time_frame',
        'is_active',
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
            'is_active' => 'boolean',
            'extracted_from_text' => 'boolean',
        ];
    }

    /**
     * Get the company that owns the goal.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the KPIs for the goal.
     */
    public function kpis(): HasMany
    {
        return $this->hasMany(GoalKpi::class);
    }
}


