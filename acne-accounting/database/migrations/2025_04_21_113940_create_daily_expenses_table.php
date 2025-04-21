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
        Schema::create('daily_expenses', function (Blueprint $table) {
            $table->id();
            $table->date('operation_date')->comment('Дата расхода');
            $table->foreignId('buyer_id')->constrained('users')->comment('Баер, совершивший расход'); // No cascade/set null? Assumes buyer must exist.
            $table->string('category', 50)->comment('Категория расхода (PROXY, CREO, ACCOUNTS, OTHER)');
            $table->decimal('quantity', 15, 2)->comment('Количество');
            $table->decimal('tariff', 15, 2)->default(1.00)->comment('Тариф');
            $table->decimal('total', 15, 2)->comment('Общая сумма (quantity * tariff)');
            $table->text('comment')->nullable()->comment('Комментарий');
            $table->foreignId('created_by')->constrained('users')->comment('Кто внес запись'); // No cascade/set null? Assumes creator must exist.
            $table->timestamps();
        });

        // Add table comment if using MySQL 8+
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `daily_expenses` comment 'Операции: Ежедневные расходы'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys first if exists
        if (Schema::hasTable('daily_expenses')) {
            Schema::table('daily_expenses', function (Blueprint $table) {
                $foreignKeys = Schema::getConnection()->getDoctrineSchemaManager()->listTableForeignKeys('daily_expenses');
                $keysToDrop = [];
                foreach ($foreignKeys as $foreignKey) {
                    if (in_array($foreignKey->getColumns(), [['buyer_id'], ['created_by']])) {
                        $keysToDrop[] = $foreignKey->getName(); // Store the actual key name for dropping
                    }
                }
                // Drop all identified foreign keys
                foreach ($keysToDrop as $keyName) {
                     // Laravel's dropForeign usually expects column name array, but dropping by actual name might be safer if convention varies
                     try {
                         // Attempt to drop by conventional name first
                         if(str_contains($keyName, 'buyer_id')) $table->dropForeign(['buyer_id']);
                         elseif(str_contains($keyName, 'created_by')) $table->dropForeign(['created_by']);
                         else DB::statement('ALTER TABLE daily_expenses DROP FOREIGN KEY ' . $keyName);
                     } catch (\Exception $e) {
                         // Fallback if conventional name fails
                         DB::statement('ALTER TABLE daily_expenses DROP FOREIGN KEY ' . $keyName);
                     }
                }
            });
        }
        Schema::dropIfExists('daily_expenses');
    }
};
