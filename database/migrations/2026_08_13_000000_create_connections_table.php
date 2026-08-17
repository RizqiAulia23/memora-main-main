<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->unique(['sender_id', 'receiver_id']);
            $table->index(['sender_id', 'status']);
            $table->index(['receiver_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('connections');
    }
};
