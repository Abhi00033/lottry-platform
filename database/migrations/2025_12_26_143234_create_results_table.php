<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->dateTime('draw_time'); // Matches your datetime cast
            $table->string('result_number', 4); // Stores the winning number (e.g., 0098)
            $table->string('series'); // Stores the winning series (e.g., 0000)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
