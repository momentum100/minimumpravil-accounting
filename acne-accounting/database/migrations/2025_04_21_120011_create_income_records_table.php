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
        Schema::create('income_records', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 15, 2)->comment('Сумма дохода');
            $table->date('income_date')->comment('Дата получения дохода (из Keitaro)');
            // created_at will represent the fetch time
            $table->timestamp('created_at')->nullable()->comment('Дата и время получения данных из Keitaro');
            $table->timestamp('updated_at')->nullable()->comment('Дата и время обновления записи'); // Added updated_at
        });

        // Add table comment if using MySQL 8+
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `income_records` comment 'Записи о доходах из Keitaro'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('income_records');
    }
};
