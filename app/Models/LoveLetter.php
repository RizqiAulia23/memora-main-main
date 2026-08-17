<?php

namespace App\Models;

use App\Enums\LoveLetterMood;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoveLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'receiver_id',
        'title',
        'content',
        'mood',
        'letter_date',
        'is_pinned',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'letter_date' => 'date',
            'is_pinned' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    protected function mood(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => LoveLetterMood::from($value),
            set: fn (LoveLetterMood|string $value) => $value instanceof LoveLetterMood ? $value->value : $value,
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function scopePinnedFirst(Builder $query): Builder
    {
        return $query->orderByDesc('is_pinned')->latest('letter_date');
    }

    public function scopeUnreadFirst(Builder $query): Builder
    {
        return $query
            ->orderByRaw('CASE WHEN read_at IS NULL THEN 0 ELSE 1 END')
            ->latest('letter_date');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, function (Builder $query, string $search) {
            $query->where(function (Builder $query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        });
    }
}
