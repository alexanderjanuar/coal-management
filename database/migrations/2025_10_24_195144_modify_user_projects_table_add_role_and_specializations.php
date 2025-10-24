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
        // Cek apakah kolom role ada sebelum drop
        if (Schema::hasColumn('user_projects', 'role')) {
            // Untuk MySQL, convert enum ke varchar dulu
            DB::statement('ALTER TABLE user_projects MODIFY role VARCHAR(255) NULL');
            
            Schema::table('user_projects', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }

        Schema::table('user_projects', function (Blueprint $table) {
            // Tambah kolom role baru sebagai string untuk fleksibilitas
            if (!Schema::hasColumn('user_projects', 'role')) {
                $table->string('role')->nullable()->after('project_id');
            }
            
            // Tambah kolom specializations untuk area spesialisasi
            if (!Schema::hasColumn('user_projects', 'specializations')) {
                $table->text('specializations')->nullable()->after('role');
            }
            
            // Tambah kolom assigned_date untuk tanggal penugasan
            if (!Schema::hasColumn('user_projects', 'assigned_date')) {
                $table->date('assigned_date')->nullable()->after('specializations');
            }
        });

        // Bersihkan data duplikat sebelum menambahkan unique constraint
        $this->removeDuplicates();

        // Tambah unique constraint dan index
        Schema::table('user_projects', function (Blueprint $table) {
            try {
                // Cek apakah unique constraint sudah ada
                $indexExists = collect(DB::select("SHOW INDEXES FROM user_projects WHERE Key_name = 'user_project_unique'"))->isNotEmpty();
                
                if (!$indexExists) {
                    $table->unique(['user_id', 'project_id'], 'user_project_unique');
                }
            } catch (\Exception $e) {
                // Jika error, skip unique constraint
                echo "Warning: Could not add unique constraint - " . $e->getMessage() . "\n";
            }

            try {
                // Cek apakah index sudah ada
                $indexExists = collect(DB::select("SHOW INDEXES FROM user_projects WHERE Key_name = 'project_user_index'"))->isNotEmpty();
                
                if (!$indexExists) {
                    $table->index(['project_id', 'user_id'], 'project_user_index');
                }
            } catch (\Exception $e) {
                // Jika error, skip index
                echo "Warning: Could not add index - " . $e->getMessage() . "\n";
            }
        });
    }

    /**
     * Remove duplicate entries, keeping only the latest one
     */
    private function removeDuplicates()
    {
        // Temukan semua duplikat
        $duplicates = DB::select("
            SELECT user_id, project_id, COUNT(*) as count 
            FROM user_projects 
            GROUP BY user_id, project_id 
            HAVING count > 1
        ");

        foreach ($duplicates as $duplicate) {
            // Ambil semua record untuk kombinasi user_id dan project_id ini
            $records = DB::table('user_projects')
                ->where('user_id', $duplicate->user_id)
                ->where('project_id', $duplicate->project_id)
                ->orderBy('id', 'desc')
                ->get();

            // Hapus semua kecuali yang pertama (terbaru)
            $keepId = $records->first()->id;
            
            DB::table('user_projects')
                ->where('user_id', $duplicate->user_id)
                ->where('project_id', $duplicate->project_id)
                ->where('id', '!=', $keepId)
                ->delete();

            echo "Removed duplicates for user_id={$duplicate->user_id}, project_id={$duplicate->project_id}, kept id={$keepId}\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_projects', function (Blueprint $table) {
            // Drop index dan constraint jika ada
            try {
                $table->dropIndex('project_user_index');
            } catch (\Exception $e) {
                // Ignore jika tidak ada
            }
            
            try {
                $table->dropUnique('user_project_unique');
            } catch (\Exception $e) {
                // Ignore jika tidak ada
            }
            
            // Drop kolom baru jika ada
            if (Schema::hasColumn('user_projects', 'specializations')) {
                $table->dropColumn('specializations');
            }
            
            if (Schema::hasColumn('user_projects', 'assigned_date')) {
                $table->dropColumn('assigned_date');
            }
            
            if (Schema::hasColumn('user_projects', 'role')) {
                $table->dropColumn('role');
            }
        });

        // Kembalikan kolom role enum seperti semula
        Schema::table('user_projects', function (Blueprint $table) {
            if (!Schema::hasColumn('user_projects', 'role')) {
                $table->enum('role', ['direktur', 'person-in-charge', 'staff'])->after('project_id');
            }
        });
    }
};