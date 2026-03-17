<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('bets', function (Blueprint $table) {
            // Link to the balance ledger
            $table->unsignedBigInteger('transaction_id')->nullable()->after('user_id');

            // Financial tracking
            $table->decimal('unit_price', 10, 2)->after('points');
            $table->decimal('total_amount', 10, 2)->after('unit_price');

            // Foreign key constraint
            $table->foreign('transaction_id')->references('id')->on('user_balance_transactions')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('bets', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
            $table->dropColumn(['transaction_id', 'unit_price', 'total_amount']);
        });
    }
};
