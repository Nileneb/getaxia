<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiSnapshot extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $table = 'kpi_snapshots';

    protected $fillable = [
        'id',
        'run_id',
        'goal_kpi_id',
        'current_value',
        'target_value',
        'unit',
        'is_top_kpi',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'is_top_kpi' => 'boolean',
            'created_at' => 'datetime',
            'current_value' => 'decimal:6',
            'target_value' => 'decimal:6',
        ];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(Run::class);
    }

    public function goalKpi(): BelongsTo
    {
        return $this->belongsTo(GoalKpi::class);
    }
}
