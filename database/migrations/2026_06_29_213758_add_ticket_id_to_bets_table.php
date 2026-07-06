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

            $table->foreignId('ticket_id')
                ->nullable()
                ->after('transaction_id')
                ->constrained('bet_tickets')
                ->nullOnDelete();

            $table->index('ticket_id');
        });
    }

    public function down(): void
    {
        Schema::table('bets', function (Blueprint $table) {

            $table->dropForeign(['ticket_id']);
            $table->dropIndex(['ticket_id']);
            $table->dropColumn('ticket_id');
        });
    }
};
