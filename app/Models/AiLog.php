<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'run_id',
        'prompt_type',
        'system_prompt_id',
        'input_context',
        'response',
        'tokens_used',
        'duration_ms',
        'success',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'input_context' => 'array',
            'response' => 'array',
            'tokens_used' => 'integer',
            'duration_ms' => 'integer',
            'success' => 'boolean',
        ];
    }

    /**
     * Get the run for this log.
     */
    public function run(): BelongsTo
    {
        return $this->belongsTo(Run::class);
    }

    /**
     * Get the system prompt used.
     */
    public function systemPrompt(): BelongsTo
    {
        return $this->belongsTo(SystemPrompt::class);
    }
}

