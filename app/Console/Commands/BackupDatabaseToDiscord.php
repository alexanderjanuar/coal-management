<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackupDatabaseToDiscord extends Command
{
    protected $signature   = 'backup:discord-db';
    protected $description = 'Dump the database and send it to Discord via webhook';

    public function handle(): int
    {
        $webhookUrl = env('DISCORD_BACKUP_WEBHOOK_URL');

        if (empty($webhookUrl)) {
            $this->error('DISCORD_BACKUP_WEBHOOK_URL is not set in .env');
            return self::FAILURE;
        }

        $db       = config('database.connections.' . config('database.default'));
        $host     = $db['host'];
        $port     = $db['port'] ?? 3306;
        $database = $db['database'];
        $username = $db['username'];
        $password = $db['password'];

        $filename     = $database . '_backup_' . now()->format('Y-m-d') . '.sql.gz';
        $dumpPath     = storage_path('app/' . $filename);

        // Run mysqldump and pipe directly into gzip
        $command = sprintf(
            'MYSQL_PWD=%s mysqldump --host=%s --port=%s --user=%s %s | gzip > %s 2>&1',
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($dumpPath)
        );

        exec($command, $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($dumpPath) || filesize($dumpPath) === 0) {
            $this->error('mysqldump failed: ' . implode("\n", $output));
            Log::error('DB backup failed', ['output' => $output]);
            return self::FAILURE;
        }

        $fileSizeMB = round(filesize($dumpPath) / 1024 / 1024, 2);
        $this->info("Dump created: {$filename} ({$fileSizeMB} MB)");

        // Discord webhooks accept files up to 25MB via multipart
        if (filesize($dumpPath) > 25 * 1024 * 1024) {
            $this->warn("Compressed dump is still {$fileSizeMB} MB, exceeds Discord 25MB limit. Skipping upload.");
            @unlink($dumpPath);
            return self::FAILURE;
        }

        // Send to Discord as a file attachment with an embed message
        $appName = config('app.name');
        $env     = config('app.env');

        $payload = json_encode([
            'content' => null,
            'embeds'  => [
                [
                    'title'       => '🗄️ Weekly Database Backup',
                    'color'       => 0x2ECC71, // green
                    'description' => "Weekly backup completed successfully.",
                    'fields'      => [
                        ['name' => '📦 Database',    'value' => "`{$database}`",    'inline' => true],
                        ['name' => '📁 File',        'value' => "`{$filename}`",    'inline' => true],
                        ['name' => '📏 Size',        'value' => "{$fileSizeMB} MB", 'inline' => true],
                        ['name' => '🌍 Environment', 'value' => $env,               'inline' => true],
                    ],
                    'footer'    => ['text' => $appName],
                    'timestamp' => now()->toIso8601String(),
                ],
            ],
        ]);

        $curl = curl_init($webhookUrl);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => [
                'payload_json' => $payload,
                'file'         => new \CURLFile($dumpPath, 'application/octet-stream', $filename),
            ],
        ]);

        $response   = curl_exec($curl);
        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        @unlink($dumpPath);

        if ($httpStatus === 200 || $httpStatus === 204) {
            $this->info('Backup sent to Discord successfully.');
            Log::info('Weekly DB backup sent to Discord', ['file' => $filename, 'size_mb' => $fileSizeMB]);
            return self::SUCCESS;
        }

        $this->error("Discord upload failed (HTTP {$httpStatus}): {$response}");
        Log::error('Discord backup upload failed', ['status' => $httpStatus, 'response' => $response]);
        return self::FAILURE;
    }
}
