<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memories', function (Blueprint $table) {
            $table->index(['user_id', 'memory_date']);
        });

        Schema::table('love_letters', function (Blueprint $table) {
            $table->index(['user_id', 'letter_date']);
        });
    }

    public function down(): void
    {
        Schema::table('memories', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'memory_date']);
        });

        Schema::table('love_letters', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'letter_date']);
        });
    }
};
