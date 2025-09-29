<?php
// database/migrations/XXXX_XX_XX_XXXXXX_enhance_application_clients_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_clients', function (Blueprint $table) {
            if (!Schema::hasColumn('application_clients', 'additional_data')) {
                $table->json('additional_data')->nullable()->after('account_period')
                      ->comment('Data tambahan yang spesifik per aplikasi');
            }
            
            if (!Schema::hasColumn('application_clients', 'notes')) {
                $table->text('notes')->nullable()->after('additional_data');
            }
            
            if (!Schema::hasColumn('application_clients', 'last_used_at')) {
                $table->timestamp('last_used_at')->nullable()->after('notes');
            }
            
            if (!Schema::hasColumn('application_clients', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('last_used_at');
            }
        });
        
        // Tambah indexes
        Schema::table('application_clients', function (Blueprint $table) {
            // Check if index exists first
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('application_clients');
            
            if (!isset($indexes['client_active_apps'])) {
                $table->index(['client_id', 'is_active'], 'client_active_apps');
            }
            
            if (!isset($indexes['app_active_clients'])) {
                $table->index(['application_id', 'is_active'], 'app_active_clients');
            }
        });
    }

    public function down(): void
    {
        Schema::table('application_clients', function (Blueprint $table) {
            // Drop indexes first
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('application_clients');
            
            if (isset($indexes['client_active_apps'])) {
                $table->dropIndex('client_active_apps');
            }
            
            if (isset($indexes['app_active_clients'])) {
                $table->dropIndex('app_active_clients');
            }
            
            // Drop columns
            $columns = ['additional_data', 'notes', 'last_used_at', 'is_active'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('application_clients', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};