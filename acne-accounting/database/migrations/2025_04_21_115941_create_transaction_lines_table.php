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
        Schema::create('transaction_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('accounts')->comment('Счет, по которому идет движение'); // No cascade/set null?
            $table->decimal('debit', 15, 2)->nullable()->default(0.00)->comment('Сумма по дебету');
            $table->decimal('credit', 15, 2)->nullable()->default(0.00)->comment('Сумма по кредиту');
            $table->string('description')->nullable()->comment('Описание строки (опционально)');
            $table->timestamps(); // Keep only created_at as per spec?

            // Indexes
            $table->index('account_id');

            // Check constraint needs to be added via raw SQL after table creation
            // $table->check('(debit >= 0 AND credit >= 0 AND (debit > 0 OR credit > 0) AND (debit = 0 OR credit = 0))', 'chk_debit_credit');
        });

        // Add CHECK constraint for MySQL 8+
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `transaction_lines` ADD CONSTRAINT `chk_debit_credit` CHECK (`debit` >= 0 AND `credit` >= 0 AND (`debit` > 0 OR `credit` > 0) AND (`debit` = 0 OR `credit` = 0))');
        }

        // Add table comment if using MySQL 8+
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `transaction_lines` comment 'Строки транзакций (дебет/кредит)'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys first if exists
        if (Schema::hasTable('transaction_lines')) {
            Schema::table('transaction_lines', function (Blueprint $table) {
                // Drop check constraint manually for MySQL
                if (Schema::getConnection()->getDriverName() === 'mysql') {
                    try {
                         DB::statement('ALTER TABLE `transaction_lines` DROP CONSTRAINT `chk_debit_credit`');
                    } catch (\Exception $e) {
                        // Ignore error if constraint doesn't exist
                    }
                }
                /*
                try {
                    $table->dropCheck('chk_debit_credit');
                } catch (\Exception $e) {
                    // Ignore errors if check constraint doesn't exist or dropping is not supported cleanly
                }
                */

                $foreignKeys = Schema::getConnection()->getDoctrineSchemaManager()->listTableForeignKeys('transaction_lines');
                $keysToDrop = [];
                foreach ($foreignKeys as $foreignKey) {
                    if (in_array($foreignKey->getColumns(), [['transaction_id'], ['account_id']])) {
                        $keysToDrop[] = $foreignKey->getName();
                    }
                }
                foreach ($keysToDrop as $keyName) {
                    try {
                        if(str_contains($keyName, 'transaction_id')) $table->dropForeign(['transaction_id']);
                        elseif(str_contains($keyName, 'account_id')) $table->dropForeign(['account_id']);
                        else DB::statement('ALTER TABLE transaction_lines DROP FOREIGN KEY ' . $keyName);
                    } catch (\Exception $e) {
                        DB::statement('ALTER TABLE transaction_lines DROP FOREIGN KEY ' . $keyName);
                    }
                }
            });
        }
        Schema::dropIfExists('transaction_lines');
    }
};
