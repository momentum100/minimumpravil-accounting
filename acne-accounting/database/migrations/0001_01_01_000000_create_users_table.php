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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('telegram_id')->nullable()->unique()->comment('ID пользователя в Telegram (null для виртуальных)');
            $table->enum('role', ['owner', 'finance', 'buyer', 'agency'])->comment('Роль пользователя');
            $table->string('name')->comment('Имя пользователя или название агентства');
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('set null')->comment('ID команды (для баеров)');
            $table->json('sub2')->nullable()->comment('Теги баера из внешней системы (в формате JSON)');
            $table->string('email')->nullable()->unique()->comment('Email для входа через веб (опционально)');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable()->comment('Хеш пароля для входа через веб');
            $table->boolean('active')->default(true)->comment('Статус активности пользователя');
            $table->boolean('is_virtual')->default(false)->comment('Признак виртуального пользователя (агентства)');
            $table->text('contact_info')->nullable()->comment('Контактная информация (для агентств)');
            $table->rememberToken();
            $table->timestamps();
        });

        // Add table comment if using MySQL 8+
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `users` comment 'Пользователи системы'");
        }

        // Keep other tables created by Breeze
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'team_id')) {
            Schema::table('users', function (Blueprint $table) {
                // Restored Jetstream team FK drop
                // Check if the foreign key exists before dropping
                // The key name format is typically `<table>_<column>_foreign`
                $foreignKeys = Schema::getConnection()->getDoctrineSchemaManager()->listTableForeignKeys('users');
                $hasForeignKey = false;
                foreach ($foreignKeys as $foreignKey) {
                    if ($foreignKey->getColumns() === ['team_id']) {
                        $hasForeignKey = true;
                        break;
                    }
                }
                if ($hasForeignKey) {
                    $table->dropForeign(['team_id']);
                }
            });
        }

        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
