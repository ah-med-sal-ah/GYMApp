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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained('gyms')->cascadeOnDelete();
            $table->string('photo')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->unsignedTinyInteger('age');
            $table->string('email');
            $table->string('phone');
            $table->string('registration_type');
            $table->date('registration_start');
            $table->date('registration_end');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['gym_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
