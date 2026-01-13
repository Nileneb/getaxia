<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebhookPreset extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'webhook_url',
        'description',
        'config_json',
        'is_active',
        'is_default',
        'version',
        'created_by_run_id',
        'rollback_to_version_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'config_json' => 'array',
            'version' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdByRun(): BelongsTo
    {
        return $this->belongsTo(Run::class, 'created_by_run_id');
    }

    public function rollbackToVersion(): BelongsTo
    {
        return $this->belongsTo(WebhookPreset::class, 'rollback_to_version_id');
    }

    /**
     * Set this preset as active and deactivate others for this user
     */
    public function activate(): void
    {
        // Deactivate all other presets for this user
        static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_active' => false]);

        // Activate this preset
        $this->update(['is_active' => true]);

        // Update user's n8n_webhook_url
        $this->user->update(['n8n_webhook_url' => $this->webhook_url]);
    }

    /**
     * Create a new version of this preset
     */
    public function createNewVersion(array $changes, ?string $runId = null): self
    {
        $latestVersion = static::where('user_id', $this->user_id)
            ->where('name', $this->name)
            ->max('version') ?? 0;

        return static::create([
            'user_id' => $this->user_id,
            'name' => $this->name,
            'webhook_url' => $changes['webhook_url'] ?? $this->webhook_url,
            'description' => $changes['description'] ?? $this->description,
            'config_json' => $changes['config_json'] ?? $this->config_json,
            'version' => $latestVersion + 1,
            'created_by_run_id' => $runId,
            'is_active' => false,
        ]);
    }

    /**
     * Rollback to a previous version
     */
    public function rollbackTo(self $targetVersion): self
    {
        $newVersion = $this->createNewVersion([
            'webhook_url' => $targetVersion->webhook_url,
            'description' => $targetVersion->description,
            'config_json' => $targetVersion->config_json,
        ]);

        $newVersion->update(['rollback_to_version_id' => $targetVersion->id]);

        return $newVersion;
    }

    /**
     * Get all versions for this preset name
     */
    public function getVersionHistory()
    {
        return static::where('user_id', $this->user_id)
            ->where('name', $this->name)
            ->orderBy('version', 'desc')
            ->get();
    }
}
