<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shared_memories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('memory_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained('users')->cascadeOnDelete();
            $table->unique(['memory_id', 'partner_id']);
            $table->index('partner_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shared_memories');
    }
};
