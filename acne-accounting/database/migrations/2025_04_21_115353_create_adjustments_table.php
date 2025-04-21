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
        Schema::create('adjustments', function (Blueprint $table) {
            $table->id();
            $table->date('entry_date')->comment('Дата внесения корректировки');
            $table->foreignId('buyer_id')->constrained('users')->comment('Баер, к которому относится корректировка'); // No cascade/set null?
            $table->string('category', 50)->comment('Категория (напр., AGENCY_1)');
            $table->string('adjustment_period', 7)->comment('Период корректировки (YYYY.MM)');
            $table->decimal('amount', 15, 2)->comment('Сумма корректировки (может быть отрицательной)');
            $table->text('comment')->comment('Комментарий/причина корректировки');
            $table->foreignId('created_by')->constrained('users')->comment('Кто внес запись'); // No cascade/set null?
            $table->timestamps();
        });

        // Add table comment if using MySQL 8+
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `adjustments` comment 'Операции: Корректировки расходов'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys first if exists
        if (Schema::hasTable('adjustments')) {
            Schema::table('adjustments', function (Blueprint $table) {
                $foreignKeys = Schema::getConnection()->getDoctrineSchemaManager()->listTableForeignKeys('adjustments');
                $keysToDrop = [];
                foreach ($foreignKeys as $foreignKey) {
                    if (in_array($foreignKey->getColumns(), [['buyer_id'], ['created_by']])) {
                        $keysToDrop[] = $foreignKey->getName(); // Store the actual key name for dropping
                    }
                }
                // Drop all identified foreign keys
                foreach ($keysToDrop as $keyName) {
                     try {
                         if(str_contains($keyName, 'buyer_id')) $table->dropForeign(['buyer_id']);
                         elseif(str_contains($keyName, 'created_by')) $table->dropForeign(['created_by']);
                         else DB::statement('ALTER TABLE adjustments DROP FOREIGN KEY ' . $keyName);
                     } catch (\Exception $e) {
                         DB::statement('ALTER TABLE adjustments DROP FOREIGN KEY ' . $keyName);
                     }
                }
            });
        }
        Schema::dropIfExists('adjustments');
    }
};
