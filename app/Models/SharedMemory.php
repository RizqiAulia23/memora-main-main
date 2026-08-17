<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SharedMemory extends Model
{
    use HasFactory;

    protected $fillable = [
        'memory_id',
        'partner_id',
    ];

    public function memory(): BelongsTo
    {
        return $this->belongsTo(Memory::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }
}
