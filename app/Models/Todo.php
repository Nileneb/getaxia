<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Todo extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'run_id',
        'raw_input',
        'normalized_title',
        'title',
        'description',
        'owner_user_id',
        'due_date',
        'source',
        'position',
        'status',
        'parent_id',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'position' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Todo::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Todo::class, 'parent_id');
    }

    /**
     * Get the run that owns the todo.
     */
    public function run(): BelongsTo
    {
        return $this->belongsTo(Run::class);
    }

    /**
     * Get the evaluation for the todo.
     */
    public function evaluation(): HasOne
    {
        return $this->hasOne(TodoEvaluation::class);
    }
}


