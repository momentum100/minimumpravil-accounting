<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade')->comment('ID пользователя-владельца счета (null для системных счетов)');
            $table->string('account_type', 50)->comment('Тип счета (напр., SYSTEM, AGENCY, BUYER_MAIN)');
            $table->string('description')->nullable()->comment('Описание счета (например, SYSTEM_ROMA, AGENCY_1, Buyer Vasya Main)');
            $table->string('currency', 3)->default('USD')->comment('Валюта счета');
            $table->timestamps();
        });

        // Add table comment if using MySQL 8+
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `accounts` comment 'Счета в системе (баланс вычисляется)'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key first if exists
        if (Schema::hasTable('accounts') && Schema::hasColumn('accounts', 'user_id')) {
            Schema::table('accounts', function (Blueprint $table) {
                $foreignKeys = Schema::getConnection()->getDoctrineSchemaManager()->listTableForeignKeys('accounts');
                $hasForeignKey = false;
                foreach ($foreignKeys as $foreignKey) {
                    if ($foreignKey->getColumns() === ['user_id']) {
                        $hasForeignKey = true;
                        break;
                    }
                }
                if ($hasForeignKey) {
                    $table->dropForeign(['user_id']);
                }
            });
        }
        Schema::dropIfExists('accounts');
    }
};
