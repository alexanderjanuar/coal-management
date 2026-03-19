<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('department_id')
                ->nullable()
                ->after('email')
                ->constrained('departments')
                ->nullOnDelete();
        });

        // Migrate existing department string values to department_id
        $departments = DB::table('departments')->pluck('id', 'name');

        foreach ($departments as $name => $id) {
            DB::table('users')
                ->where('department', $name)
                ->update(['department_id' => $id]);
        }

        // Drop the old string column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('department');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('department')->nullable()->after('email');
        });

        // Restore data from department_id back to string
        $users = DB::table('users')
            ->whereNotNull('department_id')
            ->join('departments', 'users.department_id', '=', 'departments.id')
            ->select('users.id', 'departments.name')
            ->get();

        foreach ($users as $user) {
            DB::table('users')
                ->where('id', $user->id)
                ->update(['department' => $user->name]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
        });
    }
};
