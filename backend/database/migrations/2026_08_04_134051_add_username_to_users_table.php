<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
        });

        $usedUsernames = [];

        foreach (DB::table('users')->orderBy('id')->get(['id', 'name']) as $user) {
            $base = Str::slug($user->name, '') ?: 'user';
            $username = $base;
            $suffix = 1;

            while (in_array($username, $usedUsernames, true)) {
                $suffix++;
                $username = $base.$suffix;
            }

            $usedUsernames[] = $username;

            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
