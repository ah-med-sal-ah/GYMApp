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
        Schema::table('gyms', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->string('phone')->nullable()->after('email');
        });

        $usedUsernames = [];

        foreach (DB::table('gyms')->orderBy('id')->get(['id', 'name']) as $gym) {
            $base = Str::slug($gym->name, '') ?: 'gym';
            $username = $base;
            $suffix = 1;

            while (in_array($username, $usedUsernames, true)) {
                $suffix++;
                $username = $base.$suffix;
            }

            $usedUsernames[] = $username;

            DB::table('gyms')->where('id', $gym->id)->update(['username' => $username]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gyms', function (Blueprint $table) {
            $table->dropColumn(['username', 'phone']);
        });
    }
};
