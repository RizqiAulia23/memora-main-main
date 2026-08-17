<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SharedEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'partner_id',
        'title',
        'description',
        'event_date',
        'event_time',
        'location',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
        ];
    }

    public function getEventTimeAttribute(?string $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }
}
