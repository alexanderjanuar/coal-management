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
        // First, add credential_id column to clients table
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('credential_id')
                  ->nullable()
                  ->after('ar_id')
                  ->constrained('client_credentials')
                  ->nullOnDelete()
                  ->comment('Reference to client credential');
        });

        // Get clients that have core tax credentials
        $clients = DB::table('clients')
            ->whereNotNull('core_tax_user_id')
            ->orWhereNotNull('core_tax_password')
            ->get();

        foreach ($clients as $client) {
            // Skip jika sudah ada credential untuk client ini
            $existingCredential = DB::table('client_credentials')
                ->where('credential_type', 'general')
                ->where('core_tax_user_id', $client->core_tax_user_id)
                ->where('core_tax_password', $client->core_tax_password)
                ->first();

            if (!$existingCredential) {
                // Create new credential record
                $credentialId = DB::table('client_credentials')->insertGetId([
                    'core_tax_user_id' => $client->core_tax_user_id,
                    'core_tax_password' => $client->core_tax_password,
                    'credential_type' => 'general',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Update client with credential_id
                DB::table('clients')
                    ->where('id', $client->id)
                    ->update(['credential_id' => $credentialId]);
            } else {
                // Use existing credential
                DB::table('clients')
                    ->where('id', $client->id)
                    ->update(['credential_id' => $existingCredential->id]);
            }
        }

        // Finally, drop the old columns from clients table
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['core_tax_user_id', 'core_tax_password']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the old columns to clients table
        Schema::table('clients', function (Blueprint $table) {
            $table->string('core_tax_user_id')->nullable()->after('ar_id');
            $table->string('core_tax_password')->nullable()->after('core_tax_user_id');
        });

        // Move data back from client_credentials to clients
        $clients = DB::table('clients')
            ->whereNotNull('credential_id')
            ->get();

        foreach ($clients as $client) {
            $credential = DB::table('client_credentials')
                ->where('id', $client->credential_id)
                ->where('credential_type', 'general')
                ->first();

            if ($credential) {
                DB::table('clients')
                    ->where('id', $client->id)
                    ->update([
                        'core_tax_user_id' => $credential->core_tax_user_id,
                        'core_tax_password' => $credential->core_tax_password,
                    ]);
            }
        }

        // Drop the credential_id foreign key and column
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['credential_id']);
            $table->dropColumn('credential_id');
        });
    }
};