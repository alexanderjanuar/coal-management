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
        // Pindahkan data dari clients ke client_credentials
        $clients = DB::table('clients')
            ->whereNotNull('core_tax_user_id')
            ->orWhereNotNull('core_tax_password')
            ->get();

        foreach ($clients as $client) {
            // Skip jika sudah ada credential untuk client ini
            $existingCredential = DB::table('client_credentials')
                ->where('client_id', $client->id)
                ->where('credential_type', 'general')
                ->first();

            if (!$existingCredential) {
                DB::table('client_credentials')->insert([
                    'core_tax_user_id' => $client->core_tax_user_id,
                    'core_tax_password' => $client->core_tax_password,
                    'credential_type' => 'general',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Hapus kolom lama dari tabel clients
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['core_tax_user_id', 'core_tax_password']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tambahkan kembali kolom ke tabel clients
        Schema::table('clients', function (Blueprint $table) {
            $table->string('core_tax_user_id')->nullable()->after('ar_id');
            $table->string('core_tax_password')->nullable()->after('core_tax_user_id');
        });

        // Pindahkan data kembali dari client_credentials ke clients
        $credentials = DB::table('client_credentials')
            ->where('credential_type', 'general')
            ->whereNotNull('core_tax_user_id')
            ->get();

        foreach ($credentials as $credential) {
            DB::table('clients')
                ->where('id', $credential->client_id)
                ->update([
                    'core_tax_user_id' => $credential->core_tax_user_id,
                    'core_tax_password' => $credential->core_tax_password,
                ]);
        }
    }
};