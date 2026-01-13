<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemPrompt extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'type',
        'system_message',
        'user_prompt_template',
        'temperature',
        'is_active',
        'version',
        'is_system_default',
    ];

    protected function casts(): array
    {
        return [
            'temperature' => 'decimal:1',
            'is_active' => 'boolean',
            'is_system_default' => 'boolean',
        ];
    }

    /**
     * Scope to get active prompts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get prompts by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the active prompt for a type
     */
    public static function getActiveForType(string $type): ?self
    {
        return static::ofType($type)
            ->active()
            ->latest()
            ->first();
    }
}

