<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_balance_transactions', function (Blueprint $table) {
            $table->id();

            // user reference
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // transaction type (example: add, deduct, commission...)
            $table->string('type', 30);

            // amount
            $table->decimal('amount', 10, 2);

            // balance after this transaction was performed
            $table->decimal('balance_after', 10, 2)->nullable();

            // remarks
            $table->string('remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_balance_transactions');
    }
};
