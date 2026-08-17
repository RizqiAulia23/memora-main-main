<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('important_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('partner_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->date('date');
            $table->string('type', 20)->default('custom');
            $table->text('description')->nullable();
            $table->boolean('recurring')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'date']);
            $table->index(['partner_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('important_dates');
    }
};
