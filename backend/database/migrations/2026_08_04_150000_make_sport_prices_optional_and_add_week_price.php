<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sports', function (Blueprint $table) {
            $table->renameColumn('session_price', 'day_price');
        });

        Schema::table('sports', function (Blueprint $table) {
            $table->decimal('day_price', 10, 2)->nullable()->change();
            $table->decimal('month_price', 10, 2)->nullable()->change();
            $table->decimal('year_price', 10, 2)->nullable()->change();
            $table->decimal('week_price', 10, 2)->nullable()->after('day_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sports', function (Blueprint $table) {
            $table->dropColumn('week_price');
            $table->decimal('day_price', 10, 2)->nullable(false)->change();
            $table->decimal('month_price', 10, 2)->nullable(false)->change();
            $table->decimal('year_price', 10, 2)->nullable(false)->change();
        });

        Schema::table('sports', function (Blueprint $table) {
            $table->renameColumn('day_price', 'session_price');
        });
    }
};
