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
        Schema::table('bets', function (Blueprint $table) {
            // 1. Fix the "Data too long" error: Change varchar(2) to varchar(10)
            $table->string('number', 10)->change();

            // 2. Add the new 'series_group' column
            // We place it AFTER 'series_id' so it sits logically before 'number'
            if (!Schema::hasColumn('bets', 'series_group')) {
                $table->string('series_group', 10)->nullable()->after('series_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bets', function (Blueprint $table) {
            // Drop the column if we roll back
            $table->dropColumn('series_group');

            // Optional: Revert number length (usually safer to leave it or skip this line)
            // $table->string('number', 2)->change();
        });
    }
};
