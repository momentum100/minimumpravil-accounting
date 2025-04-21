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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            // Polymorphic relationship for the source operation
            $table->unsignedBigInteger('operation_id')->comment('ID исходной операции');
            $table->string('operation_type')->comment('Тип исходной операции (напр., App\\Models\\DailyExpense)');
            // Transaction details
            $table->dateTime('transaction_date')->comment('Дата и время транзакции');
            $table->string('description')->comment('Описание транзакции (генерируется из операции)');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('completed')->comment('Статус транзакции');
            $table->string('accounting_period', 7)->comment('Учетный период (YYYY.MM), важен для корректировок');
            $table->timestamps();

            // Indexes
            $table->index(['operation_id', 'operation_type']);
            $table->index('accounting_period');
            $table->index('transaction_date');
        });

        // Add table comment if using MySQL 8+
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `transactions` comment 'Транзакции двойной записи'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
