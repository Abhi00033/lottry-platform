<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bet_tickets', function (Blueprint $table) {

            $table->boolean('is_claimed')
                ->default(false)
                ->after('status');

            $table->decimal('claim_amount', 12, 2)
                ->default(0)
                ->after('is_claimed');

            $table->timestamp('claimed_at')
                ->nullable()
                ->after('claim_amount');

            $table->foreignId('claimed_by')
                ->nullable()
                ->after('claimed_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bet_tickets', function (Blueprint $table) {

            $table->dropForeign(['claimed_by']);

            $table->dropColumn([
                'is_claimed',
                'claim_amount',
                'claimed_at',
                'claimed_by'
            ]);
        });
    }
};
