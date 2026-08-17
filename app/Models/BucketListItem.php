<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BucketListItem extends Model
{
    use HasFactory;

    public const PLANNED = 'planned';

    public const COMPLETED = 'completed';

    protected $fillable = [
        'user_id',
        'partner_id',
        'title',
        'description',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === self::COMPLETED;
    }
}
