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
        Schema::create('bet_tickets', function (Blueprint $table) {
            $table->id();

            $table->string('ticket_no', 30)->unique();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('transaction_id')
                ->constrained('user_balance_transactions')
                ->cascadeOnDelete();

            $table->date('draw_date');
            $table->time('draw_time');

            $table->decimal('ticket_price', 10, 2);

            $table->unsignedInteger('total_qty')->default(0);

            $table->decimal('total_amount', 12, 2);

            $table->enum('status', [
                'active',
                'cancelled',
                'claimed'
            ])->default('active');

            $table->timestamp('printed_at')->nullable();

            $table->timestamps();

            $table->index('ticket_no');
            $table->index('draw_date');
            $table->index('draw_time');
            $table->index('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bet_tickets');
    }
};
