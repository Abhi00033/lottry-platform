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
        Schema::create('bets', function (Blueprint $table) {
            $table->id();
            // Foreign key to users table
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Foreign key to series_master table
            $table->foreignId('series_id')->constrained('series_master')->onDelete('cascade');

            // The specific number chosen (e.g., 00 to 99)
            $table->string('number', 2);
            // Quantity entered in the input grid
            $table->integer('qty');
            // Calculated points (qty * rate/multiplier)
            $table->decimal('points', 12, 2);

            // The specific 15-min interval draw time
            $table->dateTime('draw_time');

            // Status: e.g., 'pending', 'win', 'loss', 'cancelled'
            $table->string('status')->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bets');
    }
};
