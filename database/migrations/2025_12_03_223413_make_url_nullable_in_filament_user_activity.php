<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_activities', function (Blueprint $table) {
            if (Schema::hasColumn('user_activities', 'url')) {
                // If column exists, make it nullable
                $table->string("url")->nullable()->change();
            } else {
                // If column doesn't exist, create it as nullable
                $table->string("url")->nullable()->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_activities', function (Blueprint $table) {
            if (Schema::hasColumn('user_activities', 'url')) {
                // If column exists, make it non-nullable (or drop it if it was created)
                // You can choose to drop it or make it non-nullable
                $table->dropColumn('url');
            }
        });
    }
};