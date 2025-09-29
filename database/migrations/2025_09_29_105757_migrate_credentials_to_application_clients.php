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
        // Check if application_clients table exists
        if (!Schema::hasTable('application_clients')) {
            throw new \Exception('Table application_clients does not exist. Please run the create_application_clients_table migration first.');
        }

        // Get all credentials that are linked to clients
        $credentialsWithClients = DB::table('client_credentials')
            ->join('clients', 'clients.credential_id', '=', 'client_credentials.id')
            ->select(
                'client_credentials.*',
                'clients.id as client_id',
                'clients.name as client_name'
            )
            ->whereNotNull('clients.credential_id')
            ->get();

        $totalMigrated = 0;

        foreach ($credentialsWithClients as $credential) {
            // Get full client information
            $clientInfo = DB::table('clients')->where('id', $credential->client_id)->first();
            
            if (!$clientInfo) {
                \Log::warning("Client not found for credential ID: {$credential->id}");
                continue;
            }

            $clientName = $clientInfo->name ?? "Client ID: {$credential->client_id}";
            
            // Migrate Core Tax credentials
            if (!empty($credential->core_tax_user_id) && !empty($credential->core_tax_password)) {
                // Find or get Core Tax application
                $coreTaxApp = DB::table('applications')
                    ->where('name', 'Core Tax')
                    ->first();

                if (!$coreTaxApp) {
                    // Create Core Tax application if it doesn't exist
                    $coreTaxAppId = DB::table('applications')->insertGetId([
                        'name' => 'Core Tax',
                        'logo' => 'core-tax-logo.png',
                        'app_url' => 'https://coretax.pajak.go.id',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $coreTaxAppId = $coreTaxApp->id;
                }

                // Check if application_client entry already exists
                $existingEntry = DB::table('application_clients')
                    ->where('application_id', $coreTaxAppId)
                    ->where('client_id', $credential->client_id)
                    ->first();

                if (!$existingEntry) {
                    // Insert into application_clients
                    DB::table('application_clients')->insert([
                        'username' => $credential->core_tax_user_id,
                        'password' => $credential->core_tax_password,
                        'activation_code' => null,
                        'account_period' => null,
                        'application_id' => $coreTaxAppId,
                        'client_id' => $credential->client_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $totalMigrated++;
                    \Log::info("Migrated Core Tax credentials for client: {$clientName}");
                }
            }

            // Migrate DJP credentials
            if (!empty($credential->djp_account) && !empty($credential->djp_password)) {
                // Find or get DJP application
                $djpApp = DB::table('applications')
                    ->where('name', 'DJP Online')
                    ->first();

                if (!$djpApp) {
                    // Create DJP application if it doesn't exist
                    $djpAppId = DB::table('applications')->insertGetId([
                        'name' => 'DJP Online',
                        'logo' => 'djp-logo.png',
                        'app_url' => 'https://djponline.pajak.go.id',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $djpAppId = $djpApp->id;
                }

                // Check if application_client entry already exists
                $existingEntry = DB::table('application_clients')
                    ->where('application_id', $djpAppId)
                    ->where('client_id', $credential->client_id)
                    ->first();

                if (!$existingEntry) {
                    // Insert into application_clients
                    DB::table('application_clients')->insert([
                        'username' => $credential->djp_account,
                        'password' => $credential->djp_password,
                        'activation_code' => null,
                        'account_period' => null,
                        'application_id' => $djpAppId,
                        'client_id' => $credential->client_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $totalMigrated++;
                    \Log::info("Migrated DJP credentials for client: {$clientName}");
                }
            }

            // Migrate Email credentials
            if (!empty($credential->email) && !empty($credential->email_password)) {
                // Find or get Email application
                $emailApp = DB::table('applications')
                    ->where('name', 'Email')
                    ->first();

                if (!$emailApp) {
                    // Create Email application if it doesn't exist
                    $emailAppId = DB::table('applications')->insertGetId([
                        'name' => 'Email',
                        'logo' => 'email-logo.png',
                        'app_url' => 'https://mail.google.com',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $emailAppId = $emailApp->id;
                }

                // Check if application_client entry already exists
                $existingEntry = DB::table('application_clients')
                    ->where('application_id', $emailAppId)
                    ->where('client_id', $credential->client_id)
                    ->first();

                if (!$existingEntry) {
                    // Insert into application_clients
                    DB::table('application_clients')->insert([
                        'username' => $credential->email,
                        'password' => $credential->email_password,
                        'activation_code' => null,
                        'account_period' => null,
                        'application_id' => $emailAppId,
                        'client_id' => $credential->client_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $totalMigrated++;
                    \Log::info("Migrated Email credentials for client: {$clientName}");
                }
            }
        }

        \Log::info("Total credentials migrated: {$totalMigrated}");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get all application_clients entries that were created from client_credentials
        $coreTaxApp = DB::table('applications')->where('name', 'Core Tax')->first();
        $djpApp = DB::table('applications')->where('name', 'DJP Online')->first();
        $emailApp = DB::table('applications')->where('name', 'Email')->first();

        $deletedCount = 0;

        if ($coreTaxApp) {
            $deleted = DB::table('application_clients')
                ->where('application_id', $coreTaxApp->id)
                ->delete();
            $deletedCount += $deleted;
        }

        if ($djpApp) {
            $deleted = DB::table('application_clients')
                ->where('application_id', $djpApp->id)
                ->delete();
            $deletedCount += $deleted;
        }

        if ($emailApp) {
            $deleted = DB::table('application_clients')
                ->where('application_id', $emailApp->id)
                ->delete();
            $deletedCount += $deleted;
        }

        \Log::info("Rolled back {$deletedCount} application_clients entries");
    }
};