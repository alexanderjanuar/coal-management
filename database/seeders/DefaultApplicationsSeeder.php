<?php
// database/seeders/DefaultApplicationsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DefaultApplicationsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        
        $applications = [
            [
                'name' => 'Core Tax',
                'description' => 'Aplikasi perpajakan resmi DJP untuk pelaporan pajak',
                'logo' => 'applications/coretax.png',
                'app_url' => 'https://coretax.pajak.go.id',
                'category' => 'tax',
                'required_fields' => json_encode([
                    'user_id' => 'User ID Core Tax',
                    'password' => 'Password Core Tax',
                    'npwp' => 'NPWP (Optional)'
                ]),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'DJP Online',
                'description' => 'Portal DJP untuk layanan perpajakan online',
                'logo' => 'applications/djp.png',
                'app_url' => 'https://djponline.pajak.go.id',
                'category' => 'tax',
                'required_fields' => json_encode([
                    'account' => 'Username DJP',
                    'password' => 'Password DJP',
                    'npwp' => 'NPWP (Optional)'
                ]),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'e-Faktur',
                'description' => 'Aplikasi e-Faktur DJP untuk faktur pajak elektronik',
                'logo' => 'applications/efaktur.png',
                'app_url' => 'https://efaktur.pajak.go.id',
                'category' => 'tax',
                'required_fields' => json_encode([
                    'username' => 'Username e-Faktur',
                    'password' => 'Password e-Faktur',
                    'passphrase' => 'Passphrase Certificate (Optional)'
                ]),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Email Client',
                'description' => 'Email account untuk komunikasi dengan client',
                'logo' => 'applications/email.png',
                'app_url' => '',
                'category' => 'email',
                'required_fields' => json_encode([
                    'email' => 'Alamat Email',
                    'password' => 'Password Email',
                    'smtp_host' => 'SMTP Host (Optional)',
                    'smtp_port' => 'SMTP Port (Optional)'
                ]),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Accurate Online',
                'description' => 'Software akuntansi Accurate',
                'logo' => 'applications/accurate.png',
                'app_url' => 'https://accurate.id',
                'category' => 'accounting',
                'required_fields' => json_encode([
                    'database_name' => 'Nama Database',
                    'username' => 'Username',
                    'password' => 'Password',
                    'session_id' => 'Session ID (Optional)'
                ]),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
        ];

        foreach ($applications as $app) {
            DB::table('applications')->updateOrInsert(
                ['name' => $app['name']], // Check by name
                $app // Update or insert all fields
            );
        }

        $this->command->info('✅ Default applications seeded successfully!');
        $this->command->info('📋 Applications created:');
        foreach ($applications as $app) {
            $this->command->info("   - {$app['name']} ({$app['category']})");
        }
    }
}