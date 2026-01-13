<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UserContextService
{
    /**
     * Get full user context including company, goals, and settings.
     */
    public function getFullContext(User $user): array
    {
        return Cache::remember("user_context_{$user->id}", 300, function () use ($user) {
            $company = $user->company()->first();
            $goals = $user->goals()->with('kpis')->get();
            
            return [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'full_name' => $user->first_name . ' ' . $user->last_name,
                ],
                'company' => $company ? [
                    'id' => $company->id,
                    'name' => $company->name,
                    'industry' => $company->industry,
                    'size' => $company->size,
                    'smart_description' => $company->smart_description,
                ] : null,
                'goals' => $goals->map(function ($goal) {
                    return [
                        'id' => $goal->id,
                        'title' => $goal->title,
                        'description' => $goal->description,
                        'target_date' => $goal->target_date,
                        'status' => $goal->status,
                        'smart_description' => $goal->smart_description,
                        'kpis' => $goal->kpis->map(function ($kpi) {
                            return [
                                'id' => $kpi->id,
                                'name' => $kpi->name,
                                'current_value' => $kpi->current_value,
                                'target_value' => $kpi->target_value,
                                'unit' => $kpi->unit,
                            ];
                        }),
                    ];
                }),
                'preferences' => $this->getUserPreferences($user),
            ];
        });
    }

    /**
     * Get user preferences and settings.
     */
    public function getUserPreferences(User $user): array
    {
        // TODO: Implement user_settings table or use existing preferences
        return [
            'timezone' => config('app.timezone'),
            'locale' => 'de',
            'notifications_enabled' => true,
        ];
    }

    /**
     * Get IMAP emails for user.
     * 
     * @param User $user
     * @param array $options ['folder' => 'INBOX', 'limit' => 50, 'unread_only' => false]
     * @return array
     */
    public function getImapMails(User $user, array $options = []): array
    {
        // TODO: Implement IMAP integration
        // For now, return placeholder
        
        $credentials = $this->getImapCredentials($user);
        
        if (!$credentials) {
            return [
                'error' => 'No IMAP credentials configured',
                'mails' => [],
            ];
        }

        // Placeholder for IMAP implementation
        return [
            'folder' => $options['folder'] ?? 'INBOX',
            'mails' => [],
            'count' => 0,
        ];
    }

    /**
     * Get encrypted IMAP credentials for user.
     * 
     * @param User $user
     * @return array|null
     */
    private function getImapCredentials(User $user): ?array
    {
        // TODO: Add imap_credentials column to users table or create separate table
        // Should store: host, port, username, password (encrypted)
        
        return null;
    }

    /**
     * Store IMAP credentials for user.
     * 
     * @param User $user
     * @param array $credentials ['host', 'port', 'username', 'password', 'encryption']
     * @return bool
     */
    public function setImapCredentials(User $user, array $credentials): bool
    {
        // TODO: Implement IMAP credentials storage
        // Use Laravel's encrypted casts or encrypt() helper
        
        return false;
    }

    /**
     * Clear user context cache.
     */
    public function clearCache(User $user): void
    {
        Cache::forget("user_context_{$user->id}");
    }
}
