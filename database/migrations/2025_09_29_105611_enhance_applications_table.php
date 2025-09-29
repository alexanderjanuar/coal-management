<?php
// database/migrations/XXXX_XX_XX_XXXXXX_enhance_applications_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // Check dulu apakah kolom sudah ada
            if (!Schema::hasColumn('applications', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            
            if (!Schema::hasColumn('applications', 'category')) {
                $table->enum('category', [
                    'tax',        // Aplikasi perpajakan
                    'accounting', // Aplikasi akuntansi
                    'email',      // Email accounts
                    'api',        // API services
                    'other'       // Lainnya
                ])->default('other')->after('logo');
            }
            
            if (!Schema::hasColumn('applications', 'required_fields')) {
                $table->json('required_fields')->nullable()->after('category')
                      ->comment('Field-field yang wajib diisi untuk aplikasi ini');
            }
            
            if (!Schema::hasColumn('applications', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('app_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $columns = ['description', 'category', 'required_fields', 'is_active'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('applications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};