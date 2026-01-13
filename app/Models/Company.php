<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Company extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'owner_user_id',
        'name',
        'business_model',
        'team_cofounders',
        'team_employees',
        'user_position',
        'customer_profile',
        'market_insights',
        'website',
        'original_smart_text',
        'extracted_from_text',
        'additional_information',
        'user_description',
        'user_target_customers',
        'user_market_info',
        'user_positioning',
        'user_competitive_notes',
    ];

    protected function casts(): array
    {
        return [
            'team_cofounders' => 'integer',
            'team_employees' => 'integer',
            'extracted_from_text' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the company.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /**
     * Get the goals for the company.
     */
    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    /**
     * Get the runs for the company.
     */
    public function runs(): HasMany
    {
        return $this->hasMany(Run::class);
    }

    /**
     * Get standalone KPIs (not linked to a goal).
     */
    public function kpis(): HasMany
    {
        return $this->hasMany(GoalKpi::class)->whereNull('goal_id');
    }

    /**
     * Get all KPIs (both from goals and standalone).
     */
    public function allKpis()
    {
        return GoalKpi::where(function($query) {
            $query->whereHas('goal', function($q) {
                $q->where('company_id', $this->id);
            })->orWhere('company_id', $this->id);
        })->get();
    }

    /**
     * Get the top KPI for the company.
     */
    public function getTopKpiAttribute()
    {
        return $this->topKpi();
    }

    /**
     * Get the top KPI for the company (method version).
     */
    public function topKpi()
    {
        return GoalKpi::where(function($query) {
            $query->whereHas('goal', function($q) {
                $q->where('company_id', $this->id);
            })->orWhere('company_id', $this->id);
        })->where('is_top_kpi', true)->first();
    }

    public function aiMetadata(): MorphMany
    {
        return $this->morphMany(AiExtractedMetadata::class, 'entity', 'entity_type', 'entity_id');
    }

    public function profiles(): HasMany
    {
        return $this->hasMany(CompanyProfile::class);
    }
}


