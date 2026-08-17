<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\ConnectionCodeService;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

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
    'connection_code',
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

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->connection_code)) {
                $user->connection_code = app(ConnectionCodeService::class)->generateUnique();
            }
        });
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

    public function events(): HasMany
    {
        return $this->hasMany(SharedEvent::class);
    }

    public function importantDates(): HasMany
    {
        return $this->hasMany(ImportantDate::class);
    }

    public function playlists(): HasMany
    {
        return $this->hasMany(SharedPlaylist::class);
    }

    public function bucketListItems(): HasMany
    {
        return $this->hasMany(BucketListItem::class);
    }

    public function sentConnections(): HasMany
    {
        return $this->hasMany(Connection::class, 'sender_id');
    }

    public function receivedConnections(): HasMany
    {
        return $this->hasMany(Connection::class, 'receiver_id');
    }

    /**
     * Users with an accepted (connected) connection in either direction.
     *
     * @return Collection<int, User>
     */
    public function connectedPartners(): Collection
    {
        return Connection::query()
            ->where(function (Builder $query) {
                $query->where('sender_id', $this->id)->where('status', Connection::ACCEPTED);
            })
            ->orWhere(function (Builder $query) {
                $query->where('receiver_id', $this->id)->where('status', Connection::ACCEPTED);
            })
            ->with(['sender', 'receiver'])
            ->get()
            ->map(fn (Connection $connection) => $connection->sender_id === $this->id
                ? $connection->receiver
                : $connection->sender)
            ->filter()
            ->values();
    }

    /**
     * Ids of users with an accepted connection in either direction.
     *
     * @return Collection<int, int>
     */
    public function connectedPartnerIds(): Collection
    {
        return Connection::query()
            ->where(function (Builder $query) {
                $query->where('sender_id', $this->id)->where('status', Connection::ACCEPTED);
            })
            ->orWhere(function (Builder $query) {
                $query->where('receiver_id', $this->id)->where('status', Connection::ACCEPTED);
            })
            ->get(['sender_id', 'receiver_id'])
            ->map(fn (Connection $connection) => $connection->sender_id === $this->id
                ? $connection->receiver_id
                : $connection->sender_id)
            ->filter()
            ->values();
    }

    /**
     * Whether the two users share an accepted connection (either direction).
     */
    public function hasAcceptedConnectionWith(User $other): bool
    {
        return Connection::query()
            ->where(function (Builder $query) use ($other) {
                $query->where('sender_id', $this->id)
                    ->where('receiver_id', $other->id)
                    ->where('status', Connection::ACCEPTED);
            })
            ->orWhere(function (Builder $query) use ($other) {
                $query->where('sender_id', $other->id)
                    ->where('receiver_id', $this->id)
                    ->where('status', Connection::ACCEPTED);
            })
            ->exists();
    }

    public function settings(): HasOne
    {
        return $this->hasOne(UserSettings::class);
    }

    public function getSettings(): UserSettings
    {
        // fresh() re-reads the created row so the returned model reflects the
        // column defaults (theme = 'light', notifications_enabled = true).
        return $this->settings ?: $this->settings()->create(['user_id' => $this->id])->fresh();
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
