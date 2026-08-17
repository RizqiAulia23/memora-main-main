<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enforce connection_code NOT NULL.
     *
     * Runs after the backfill migration, so every existing user already has a
     * code. The defensive backfill below keeps this safe even if the column
     * was added without running the dedicated backfill migration first.
     */
    public function up(): void
    {
        foreach (DB::table('users')->whereNull('connection_code')->lazyById() as $user) {
            do {
                $code = (string) random_int(10000000, 99999999);
            } while (DB::table('users')->where('connection_code', $code)->exists());

            DB::table('users')->where('id', $user->id)->update(['connection_code' => $code]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('connection_code', 8)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('connection_code', 8)->nullable()->change();
        });
    }
};
