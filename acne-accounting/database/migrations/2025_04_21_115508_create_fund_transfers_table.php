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
        Schema::create('fund_transfers', function (Blueprint $table) {
            $table->id();
            $table->date('transfer_date')->comment('Дата перемещения');
            $table->foreignId('from_account_id')->constrained('accounts')->comment('Счет-отправитель'); // No cascade/set null?
            $table->foreignId('to_account_id')->constrained('accounts')->comment('Счет-получатель'); // No cascade/set null?
            $table->decimal('amount', 15, 2)->comment('Сумма перемещения');
            $table->text('comment')->nullable()->comment('Комментарий');
            $table->foreignId('created_by')->constrained('users')->comment('Кто внес запись'); // No cascade/set null?
            $table->timestamps();
        });

        // Add table comment if using MySQL 8+
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `fund_transfers` comment 'Операции: Перемещение средств'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys first if exists
        if (Schema::hasTable('fund_transfers')) {
            Schema::table('fund_transfers', function (Blueprint $table) {
                $foreignKeys = Schema::getConnection()->getDoctrineSchemaManager()->listTableForeignKeys('fund_transfers');
                $keysToDrop = [];
                foreach ($foreignKeys as $foreignKey) {
                    if (in_array($foreignKey->getColumns(), [['from_account_id'], ['to_account_id'], ['created_by']])) {
                        $keysToDrop[] = $foreignKey->getName(); // Store the actual key name for dropping
                    }
                }
                // Drop all identified foreign keys
                foreach ($keysToDrop as $keyName) {
                     try {
                         if(str_contains($keyName, 'from_account_id')) $table->dropForeign(['from_account_id']);
                         elseif(str_contains($keyName, 'to_account_id')) $table->dropForeign(['to_account_id']);
                         elseif(str_contains($keyName, 'created_by')) $table->dropForeign(['created_by']);
                         else DB::statement('ALTER TABLE fund_transfers DROP FOREIGN KEY ' . $keyName);
                     } catch (\Exception $e) {
                         DB::statement('ALTER TABLE fund_transfers DROP FOREIGN KEY ' . $keyName);
                     }
                }
            });
        }
        Schema::dropIfExists('fund_transfers');
    }
};
