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
            $table->dropColumn('description');
            $table->string('photo')->nullable()->after('name');
            $table->decimal('session_price', 10, 2)->after('photo');
            $table->decimal('month_price', 10, 2)->after('session_price');
            $table->decimal('year_price', 10, 2)->after('month_price');
            $table->softDeletes();

            $table->unique(['gym_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sports', function (Blueprint $table) {
            $table->dropUnique(['gym_id', 'name']);
            $table->dropSoftDeletes();
            $table->dropColumn(['photo', 'session_price', 'month_price', 'year_price']);
            $table->text('description')->nullable();
        });
    }
};
