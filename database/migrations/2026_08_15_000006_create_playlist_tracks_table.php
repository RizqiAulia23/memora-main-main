<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playlist_tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playlist_id')->constrained('shared_playlists')->cascadeOnDelete();
            $table->foreignId('added_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('artist');
            $table->string('url');
            $table->string('thumbnail')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->index(['playlist_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playlist_tracks');
    }
};
