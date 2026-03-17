<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('series_master', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('series_no'); // e.g., 0000, 1000
            $blueprint->integer('start');    // 0
            $blueprint->integer('end');      // 999
            $blueprint->decimal('rate', 8, 2); // The rate shown in the UI
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('series_master');
    }
};
