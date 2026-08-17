<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SharedPlaylist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'partner_id',
        'name',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(PlaylistTrack::class, 'playlist_id')->orderBy('position')->orderBy('id');
    }
}
