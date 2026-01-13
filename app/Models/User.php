<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasUuids, Notifiable, TwoFactorAuthenticatable;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'is_guest',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_guest' => 'boolean',
        ];
    }

    /**
     * Get the user's full name
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $name = $this->full_name ?: $this->email;
        return Str::of($name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the company owned by the user.
     */
    public function company(): HasOne
    {
        return $this->hasOne(Company::class, 'owner_user_id');
    }

    /**
     * Get all goals for the user (through their company).
     */
    public function aiMetadata(): MorphMany
    {
        return $this->morphMany(AiExtractedMetadata::class, 'entity', 'entity_type', 'entity_id');
    }

    public function goals()
    {
        return $this->hasManyThrough(
            Goal::class,
            Company::class,
            'owner_user_id', // Foreign key on companies table
            'company_id',    // Foreign key on goals table
            'id',           // Local key on users table
            'id'            // Local key on companies table
        );
    }

    /**
     * Get all runs for the user's company's goals.
     */
    public function runs(): HasManyThrough
    {
        return $this->hasManyThrough(
            Run::class,
            Goal::class,
            'company_id', // Foreign key on goals table
            'goal_id',    // Foreign key on runs table
            'id',         // Local key on users table (but we go through company)
            'id'          // Local key on goals table
        )->whereHas('goal.company', function ($query) {
            $query->where('owner_user_id', $this->id);
        });
    }
}
