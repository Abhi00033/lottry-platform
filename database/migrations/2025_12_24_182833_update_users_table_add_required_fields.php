<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            /*
             |--------------------------------------------------------------------------
             | Identification & Roles
             |--------------------------------------------------------------------------
             */
            $table->uuid('unique_id')
                ->after('id')
                ->unique()
                ->comment('Public unique user identifier');

            $table->unsignedBigInteger('role_id')
                ->default(1)
                ->after('unique_id')
                ->comment('User role id');

            $table->unsignedBigInteger('general_status_id')
                ->default(1)
                ->after('role_id')
                ->comment('General status (active, inactive, blocked)');

            /*
             |--------------------------------------------------------------------------
             | Personal Information
             |--------------------------------------------------------------------------
             */
            $table->string('first_name')
                ->after('general_status_id');

            $table->string('last_name')
                ->nullable()
                ->after('first_name');

            $table->string('username')
                ->after('last_name')
                ->unique();

            $table->string('mobile', 15)
                ->nullable()
                ->after('username')
                ->unique();

            /*
             |--------------------------------------------------------------------------
             | Wallet / Balance
             |--------------------------------------------------------------------------
             */
            $table->decimal('balance', 12, 2)
                ->default(0.00)
                ->after('mobile');

            /*
             |--------------------------------------------------------------------------
             | Soft Deletes
             |--------------------------------------------------------------------------
             */
            $table->softDeletes();
        });

        /*
         |--------------------------------------------------------------------------
         | Backfill Existing Records (IMPORTANT)
         |--------------------------------------------------------------------------
         */
        DB::table('users')->whereNull('unique_id')->update([
            'unique_id' => DB::raw('(UUID())'),
            'first_name' => DB::raw('name'),
            'role_id' => 1,
            'general_status_id' => 1,
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropUnique(['unique_id']);
            $table->dropUnique(['username']);
            $table->dropUnique(['mobile']);

            $table->dropColumn([
                'unique_id',
                'role_id',
                'general_status_id',
                'first_name',
                'last_name',
                'username',
                'mobile',
                'balance',
            ]);

            $table->dropSoftDeletes();
        });
    }
};
