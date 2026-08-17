<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bucket_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('planned');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index(['partner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bucket_list_items');
    }
};
