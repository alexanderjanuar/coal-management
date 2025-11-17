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
        Schema::table('user_projects', function (Blueprint $table) {
            // Drop unique constraint
            try {
                $table->dropUnique('user_project_unique');
                echo "✓ Unique constraint 'user_project_unique' berhasil dihapus.\n";
            } catch (\Exception $e) {
                echo "✗ Error saat menghapus unique constraint: " . $e->getMessage() . "\n";
            }
            
            // Drop index jika ada
            try {
                $table->dropIndex('project_user_index');
                echo "✓ Index 'project_user_index' berhasil dihapus.\n";
            } catch (\Exception $e) {
                echo "✗ Error saat menghapus index: " . $e->getMessage() . "\n";
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_projects', function (Blueprint $table) {
            // Tambahkan kembali unique constraint
            try {
                $table->unique(['user_id', 'project_id'], 'user_project_unique');
                echo "✓ Unique constraint 'user_project_unique' berhasil ditambahkan kembali.\n";
            } catch (\Exception $e) {
                echo "✗ Error: " . $e->getMessage() . "\n";
            }
            
            // Tambahkan kembali index
            try {
                $table->index(['project_id', 'user_id'], 'project_user_index');
                echo "✓ Index 'project_user_index' berhasil ditambahkan kembali.\n";
            } catch (\Exception $e) {
                echo "✗ Error: " . $e->getMessage() . "\n";
            }
        });
    }
};