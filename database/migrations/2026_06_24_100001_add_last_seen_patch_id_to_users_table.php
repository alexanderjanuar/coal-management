<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ID patch terakhir yang sudah dilihat user — penanda agar banner patch
            // notes hanya muncul saat ada patch baru (id > last_seen_patch_id).
            $table->unsignedBigInteger('last_seen_patch_id')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_seen_patch_id');
        });
    }
};
