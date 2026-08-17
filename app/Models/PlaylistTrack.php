<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaylistTrack extends Model
{
    use HasFactory;

    protected $fillable = [
        'playlist_id',
        'added_by',
        'title',
        'artist',
        'url',
        'thumbnail',
        'position',
    ];

    public function playlist(): BelongsTo
    {
        return $this->belongsTo(SharedPlaylist::class, 'playlist_id');
    }

    public function adder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
