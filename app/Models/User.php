<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'avatar',
    'bio',
    'partner_name',
    'relationship_date',
    'location',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'relationship_date' => 'date',
        ];
    }

    public function memories(): HasMany
    {
        return $this->hasMany(Memory::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoriteMemories(): BelongsToMany
    {
        return $this->belongsToMany(Memory::class, 'favorites')->withTimestamps();
    }

    public function loveLetters(): HasMany
    {
        return $this->hasMany(LoveLetter::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(UserSettings::class);
    }

    public function getSettings(): UserSettings
    {
        return $this->settings ?: $this->settings()->create(['user_id' => $this->id]);
    }

    public function isFavorite(Memory $memory): bool
    {
        return $this->favorites()->where('memory_id', $memory->id)->exists();
    }

    public function avatarUrl(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        return route('user.avatar', $this);
    }
}
