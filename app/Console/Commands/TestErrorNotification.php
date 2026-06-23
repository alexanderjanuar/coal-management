<?php

namespace App\Console\Commands;

use App\Exceptions\Handler;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use RuntimeException;

class TestErrorNotification extends Command
{
    protected $signature = 'discord:test-error';

    protected $description = 'Kirim notifikasi error contoh ke Discord untuk menguji format & pengiriman (abaikan guard production).';

    public function handle(Handler $handler): int
    {
        if (empty(config('app.discord_webhook_url'))) {
            $this->error('DISCORD_WEBHOOK_URL belum di-set di .env — tidak ada tujuan untuk dikirim.');

            return self::FAILURE;
        }

        // Simulasikan konteks request Livewire agar notifikasi terlihat seperti error sungguhan
        // (Halaman dari referer + Komponen/Aksi dari payload), bukan request CLI kosong.
        $snapshot = json_encode(['memo' => ['name' => 'client.management.kontrak-tab']]);
        $request  = Request::create(rtrim(config('app.url'), '/') . '/livewire/update', 'POST', [
            'components' => [[
                'snapshot' => $snapshot,
                'calls'    => [['method' => 'save']],
            ]],
        ]);
        $request->headers->set('referer', rtrim(config('app.url'), '/') . '/clients/1');
        $this->laravel->instance('request', $request);

        $this->info('Mengirim notifikasi test ke Discord...');

        $exception = new RuntimeException('🧪 TEST — notifikasi error Discord berfungsi. (Pesan uji, bukan error sungguhan.)');

        $sent = $handler->sendTestWebhook($exception);

        if ($sent) {
            $this->info('✅ Terkirim. Cek channel Discord-nya.');

            return self::SUCCESS;
        }

        $this->error('❌ Gagal mengirim. Cek storage/logs/laravel.log untuk detail (URL webhook / koneksi).');

        return self::FAILURE;
    }
}
