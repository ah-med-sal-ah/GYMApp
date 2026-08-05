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
        Schema::table('coaches', function (Blueprint $table) {
            $table->unsignedTinyInteger('age')->after('phone');
            $table->decimal('salary', 10, 2)->after('age');
            $table->softDeletes();

            $table->unique(['gym_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coaches', function (Blueprint $table) {
            $table->dropUnique(['gym_id', 'email']);
            $table->dropSoftDeletes();
            $table->dropColumn(['age', 'salary']);
        });
    }
};
