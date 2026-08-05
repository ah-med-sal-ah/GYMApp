<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('coaches', function (Blueprint $table) {
            $table->foreignId('sport_id')->nullable()->after('gym_id')->constrained('sports')->nullOnDelete();
        });

        $firstSportPerCoach = DB::table('coach_sport')
            ->selectRaw('coach_id, MIN(sport_id) as sport_id')
            ->groupBy('coach_id')
            ->get();

        foreach ($firstSportPerCoach as $row) {
            DB::table('coaches')->where('id', $row->coach_id)->update(['sport_id' => $row->sport_id]);
        }

        Schema::dropIfExists('coach_sport');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('coach_sport', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coach_id')->constrained('coaches')->cascadeOnDelete();
            $table->foreignId('sport_id')->constrained('sports')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['coach_id', 'sport_id']);
        });

        Schema::table('coaches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sport_id');
        });
    }
};
