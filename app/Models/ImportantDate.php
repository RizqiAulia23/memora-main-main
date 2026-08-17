<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportantDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'partner_id',
        'title',
        'date',
        'type',
        'description',
        'recurring',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'recurring' => 'boolean',
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

    /**
     * The next upcoming occurrence of the date (or null for a past, non-recurring date).
     */
    public function nextOccurrence(): ?Carbon
    {
        $date = $this->date->copy()->startOfDay();
        $today = now()->startOfDay();

        if ($date->greaterThanOrEqualTo($today)) {
            return $date;
        }

        if (! $this->recurring) {
            return null;
        }

        $next = $date->copy();

        while ($next->lessThan($today)) {
            $next->addYear();
        }

        return $next;
    }

    /**
     * Days until the next occurrence, or null when there is no upcoming occurrence.
     */
    public function daysUntil(): ?int
    {
        $next = $this->nextOccurrence();

        return $next?->diffInDays(now()->startOfDay(), false);
    }
}
