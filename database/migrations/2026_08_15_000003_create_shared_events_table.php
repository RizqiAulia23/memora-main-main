<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shared_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('event_date');
            $table->time('event_time')->nullable();
            $table->string('location')->nullable();
            $table->string('color', 20)->nullable();
            $table->timestamps();
            $table->index(['user_id', 'event_date']);
            $table->index(['partner_id', 'event_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shared_events');
    }
};
