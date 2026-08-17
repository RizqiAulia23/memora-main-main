<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('love_letters', function (Blueprint $table) {
            $table->foreignId('receiver_id')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('read_at')->nullable()->after('is_pinned');
            $table->index('receiver_id');
        });
    }

    public function down(): void
    {
        Schema::table('love_letters', function (Blueprint $table) {
            $table->dropIndex(['receiver_id']);
            $table->dropConstrainedForeignId('receiver_id');
            $table->dropColumn('read_at');
        });
    }
};
