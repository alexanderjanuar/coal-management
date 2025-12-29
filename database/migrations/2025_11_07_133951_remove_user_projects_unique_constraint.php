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
            // First, drop foreign keys if they exist
            $foreignKeys = $this->getForeignKeys('user_projects');
            
            foreach ($foreignKeys as $foreignKey) {
                if (str_contains($foreignKey, 'user_id') || str_contains($foreignKey, 'project_id')) {
                    try {
                        $table->dropForeign($foreignKey);
                        echo "✓ Foreign key '{$foreignKey}' berhasil dihapus.\n";
                    } catch (\Exception $e) {
                        echo "✗ Error saat menghapus foreign key: " . $e->getMessage() . "\n";
                    }
                }
            }
        });

        Schema::table('user_projects', function (Blueprint $table) {
            // Now drop the unique constraint
            try {
                $table->dropUnique('user_project_unique');
                echo "✓ Unique constraint 'user_project_unique' berhasil dihapus.\n";
            } catch (\Exception $e) {
                echo "✗ Error saat menghapus unique constraint: " . $e->getMessage() . "\n";
            }
            
            // Drop index if exists
            try {
                $table->dropIndex('project_user_index');
                echo "✓ Index 'project_user_index' berhasil dihapus.\n";
            } catch (\Exception $e) {
                echo "✗ Error saat menghapus index: " . $e->getMessage() . "\n";
            }
        });

        Schema::table('user_projects', function (Blueprint $table) {
            // Re-add foreign keys
            try {
                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade');
                echo "✓ Foreign key 'user_id' berhasil ditambahkan kembali.\n";
            } catch (\Exception $e) {
                echo "✗ Error: " . $e->getMessage() . "\n";
            }

            try {
                $table->foreign('project_id')
                      ->references('id')
                      ->on('projects')
                      ->onDelete('cascade');
                echo "✓ Foreign key 'project_id' berhasil ditambahkan kembali.\n";
            } catch (\Exception $e) {
                echo "✗ Error: " . $e->getMessage() . "\n";
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_projects', function (Blueprint $table) {
            // Drop foreign keys first
            try {
                $table->dropForeign(['user_id']);
                $table->dropForeign(['project_id']);
            } catch (\Exception $e) {
                echo "✗ Error: " . $e->getMessage() . "\n";
            }
        });

        Schema::table('user_projects', function (Blueprint $table) {
            // Add back unique constraint
            try {
                $table->unique(['user_id', 'project_id'], 'user_project_unique');
                echo "✓ Unique constraint 'user_project_unique' berhasil ditambahkan kembali.\n";
            } catch (\Exception $e) {
                echo "✗ Error: " . $e->getMessage() . "\n";
            }
            
            // Add back index
            try {
                $table->index(['project_id', 'user_id'], 'project_user_index');
                echo "✓ Index 'project_user_index' berhasil ditambahkan kembali.\n";
            } catch (\Exception $e) {
                echo "✗ Error: " . $e->getMessage() . "\n";
            }
        });

        Schema::table('user_projects', function (Blueprint $table) {
            // Re-add foreign keys
            try {
                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade');
                
                $table->foreign('project_id')
                      ->references('id')
                      ->on('projects')
                      ->onDelete('cascade');
            } catch (\Exception $e) {
                echo "✗ Error: " . $e->getMessage() . "\n";
            }
        });
    }

    /**
     * Get foreign keys for a table
     */
    private function getForeignKeys(string $table): array
    {
        $foreignKeys = [];
        
        try {
            $keys = DB::select(
                "SELECT CONSTRAINT_NAME 
                 FROM information_schema.KEY_COLUMN_USAGE 
                 WHERE TABLE_SCHEMA = DATABASE() 
                 AND TABLE_NAME = ? 
                 AND REFERENCED_TABLE_NAME IS NOT NULL",
                [$table]
            );
            
            foreach ($keys as $key) {
                $foreignKeys[] = $key->CONSTRAINT_NAME;
            }
        } catch (\Exception $e) {
            // Ignore errors
        }
        
        return $foreignKeys;
    }
};