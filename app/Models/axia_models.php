<?php
// Model: Company.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Company extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'business_model',
        'stage',
        'team_size',
        'timeframe',
        'additional_info',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

// ============================================================
// Model: Goal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Goal extends Model
{
    protected $fillable = [
        'user_id',
        'external_id',
        'title',
        'description',
        'priority',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

// ============================================================
// Model: Todo.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Todo extends Model
{
    protected $fillable = [
        'user_id',
        'external_id',
        'text',
        'completed',
    ];

    protected $casts = [
        'completed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

// ============================================================
// Model: Analysis.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Analysis extends Model
{
    protected $fillable = [
        'user_id',
        'focus_score',
        'tasks_analyzed',
        'goals_count',
        'task_results',
    ];

    protected $casts = [
        'task_results' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

// ============================================================
// Add these relationships to your User model (app/Models/User.php):

// In User.php, add:

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

public function company(): HasOne
{
    return $this->hasOne(Company::class);
}

public function goals(): HasMany
{
    return $this->hasMany(Goal::class);
}

public function todos(): HasMany
{
    return $this->hasMany(Todo::class);
}

public function analyses(): HasMany
{
    return $this->hasMany(Analysis::class);
}
