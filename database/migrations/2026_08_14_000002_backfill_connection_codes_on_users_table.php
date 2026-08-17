<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('users')->whereNull('connection_code')->lazyById() as $user) {
            do {
                $code = (string) random_int(10000000, 99999999);
            } while (DB::table('users')->where('connection_code', $code)->exists());

            DB::table('users')->where('id', $user->id)->update(['connection_code' => $code]);
        }
    }

    public function down(): void
    {
        // The column itself is removed by the add-connection-code migration.
    }
};
