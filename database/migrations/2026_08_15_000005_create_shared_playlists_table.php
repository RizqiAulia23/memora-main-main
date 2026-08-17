<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shared_playlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name')->default('Our Playlist');
            $table->timestamps();
            $table->unique(['user_id', 'partner_id']);
            $table->index('partner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shared_playlists');
    }
};
