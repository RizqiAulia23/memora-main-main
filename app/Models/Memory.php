<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Memory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'image',
        'memory_date',
    ];

    protected function casts(): array
    {
        return [
            'memory_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function sharedWith(): HasMany
    {
        return $this->hasMany(SharedMemory::class);
    }

    public function isSharedWith(User $user): bool
    {
        return $this->sharedWith()->where('partner_id', $user->id)->exists();
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, function (Builder $query, string $search) {
            $query->where(function (Builder $query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        });
    }

    public function scopeSort(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'oldest' => $query->oldest('created_at'),
            'memory_date' => $query->orderByDesc('memory_date'),
            default => $query->latest('created_at'),
        };
    }

    public function scopeFavorited(Builder $query): Builder
    {
        return $query->whereHas('favorites', fn (Builder $q) => $q->where('user_id', auth()->id()));
    }

    public function scopeWithImage(Builder $query): Builder
    {
        return $query->whereNotNull('image');
    }

    public function imageUrl(): string
    {
        return $this->image
            ? route('memories.image', $this)
            : asset('img/memory-placeholder.svg');
    }
}
