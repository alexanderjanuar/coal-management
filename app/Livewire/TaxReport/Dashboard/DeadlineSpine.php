<?php

namespace App\Livewire\TaxReport\Dashboard;

use App\Models\User;
use App\Services\TaxDeadlineService;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Kenepa\Banner\Facades\BannerManager;
use Kenepa\Banner\ValueObjects\BannerData;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Tulang punggung tenggat: garis waktu bulan tempat sebuah periode jatuh tempo.
 *
 * Ini bagian pertama halaman karena prioritas pertama manajer adalah triase,
 * bukan agregat. Garis waktunya menjawab "seberapa dekat tenggatnya" secara
 * spasial, dan ketiga ruas di bawahnya menjawab "berapa yang masih tertunggak".
 *
 * Sengaja hanya mengikuti periode dan filter klien. Filter jenis pajak dan
 * status tidak diterapkan di sini: menyaring status ke "Sudah Lapor" akan
 * membuat hitungan tunggakan bertentangan dengan dirinya sendiri. Penyaringan
 * itu tugas daftar triase di bawahnya.
 */
class DeadlineSpine extends Component
{
    use Concerns\ReadsDashboardFilters;

    public ?string $focused = null;

    public function mount(TaxDeadlineService $deadlines): void
    {
        $this->hydrateFiltersFromRequest($deadlines);
    }

    /**
     * Mengklik satu ruas menyorot kewajiban itu di daftar triase. Tanpa ini
     * angka di tulang punggung hanya jadi hiasan, dan itu melanggar prinsip
     * "setiap angka punya tujuan".
     */
    public function focus(string $key): void
    {
        $this->focused = $this->focused === $key ? null : $key;

        $this->dispatch('deadlineFocused', key: $this->focused);
    }

    #[On('deadlineFocusCleared')]
    public function clearFocus(): void
    {
        $this->focused = null;
    }

    /**
     * Memberi tahu para project manager bahwa sebuah tenggat masih menyisakan
     * tunggakan, sekaligus memasang banner untuk semua pengguna.
     *
     * Dipindahkan ke sini dari TaxCalendar. Datanya memang sudah ada di ruas
     * tenggat, jadi aksinya tidak perlu grid kalender sebagai perantara.
     *
     * Aksi ini keluar dari aplikasi (notifikasi ke orang sungguhan dan banner
     * sitewide), jadi view mewajibkan konfirmasi sebelum memanggilnya.
     */
    public function sendReminder(string $key, TaxDeadlineService $service): void
    {
        $period = $this->periodDate();
        $anchor = $service->anchorFor($period);

        $deadline = collect($service->deadlinesFor($anchor, Carbon::today(), $this->clientId))
            ->firstWhere('key', $key);

        if (! $deadline || $deadline['outstanding'] === 0) {
            Notification::make()
                ->title('Tidak ada tunggakan')
                ->body('Tidak ada yang perlu diingatkan untuk tenggat ini.')
                ->warning()
                ->send();

            return;
        }

        $managers = User::whereHas('roles', fn ($q) => $q->where('name', 'project-manager'))->get();

        if ($managers->isEmpty()) {
            Notification::make()
                ->title('Tidak ada project manager')
                ->body('Tidak ditemukan pengguna dengan role project-manager, jadi pengingat tidak dikirim.')
                ->warning()
                ->send();

            return;
        }

        $verb = $key === TaxDeadlineService::PAYMENT ? 'membayar' : 'melaporkan';
        $periodLabel = $service->periodLabel($period);
        $dateLabel = $deadline['date']->day . ' ' . $service->monthName($anchor) . ' ' . $anchor->year;
        $distance = strtolower($service->humanDistance($deadline['days_remaining']));

        // Nada mengikuti PRODUCT.md: lugas, tanpa emoji, tanpa tanda seru.
        $body = "{$deadline['outstanding']} klien aktif belum {$verb} {$deadline['short']} periode {$periodLabel}. "
            . "Tenggat {$dateLabel}, {$distance}.";

        try {
            foreach ($managers as $manager) {
                Notification::make()
                    ->title('Tunggakan ' . $deadline['short'] . ' periode ' . $periodLabel)
                    ->body($body)
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color($deadline['tone'] === 'overdue' ? 'danger' : 'warning')
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('view')
                            ->label('Lihat laporan pajak')
                            ->url(route('filament.admin.resources.tax-reports.index'))
                            ->button()
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($manager)
                    ->broadcast($manager);
            }

            BannerManager::store($this->buildBanner($deadline, $body));
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->title('Pengingat gagal dikirim')
                ->body('Terjadi kesalahan saat mengirim pengingat. Coba lagi, atau periksa log bila berulang.')
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Pengingat terkirim')
            ->body('Dikirim ke ' . $managers->count() . ' project manager, dan banner dipasang untuk semua pengguna.')
            ->success()
            ->send();
    }

    /**
     * Banner memakai latar solid, bukan gradient merah seperti versi lama.
     * Warnanya sejalan dengan token dashboard: merah hanya untuk yang benar
     * benar lewat tenggat, amber untuk yang mendekat.
     */
    protected function buildBanner(array $deadline, string $body): BannerData
    {
        $isOverdue = $deadline['tone'] === 'overdue';

        return new BannerData(
            id: uniqid(),
            name: 'Tunggakan ' . $deadline['short'],
            content: $body,
            is_active: true,
            active_since: now()->format('Y-m-d'),
            icon: 'heroicon-o-exclamation-triangle',
            background_type: 'solid',
            start_color: $isOverdue ? '#B42318' : '#B54708',
            end_color: null,
            start_time: '00:00',
            end_time: '23:59',
            can_be_closed_by_user: true,
            text_color: '#FFFFFF',
            icon_color: '#FFFFFF',
            render_location: 'Header',
            scope: [],
            link_url: route('filament.admin.resources.tax-reports.index'),
            link_text: 'Lihat laporan pajak',
            link_click_action: 'redirect',
            link_button_style: 'filled',
            link_button_color: '#FFFFFF',
            link_text_color: $isOverdue ? '#B42318' : '#B54708',
            link_active: true,
            link_open_in_new_tab: false,
            link_button_icon: 'heroicon-o-arrow-right',
            link_button_icon_color: $isOverdue ? '#B42318' : '#B54708',
        );
    }

    public function render(TaxDeadlineService $service)
    {
        $period = $this->periodDate();
        $anchor = $service->anchorFor($period);
        $today = Carbon::today();

        $items = $service->deadlinesFor($anchor, $today, $this->clientId);

        // Penanda hari ini hanya berarti kalau hari ini memang jatuh di bulan
        // tenggat yang sedang ditampilkan. Untuk periode lama, tidak digambar.
        $todayInAnchor = $today->isSameMonth($anchor);

        return view('livewire.tax-report.dashboard.deadline-spine', [
            'deadlines' => $items,
            'service' => $service,
            'anchor' => $anchor,
            // Bukan 'period': Livewire menyuntikkan properti publik ke view
            // setelah data render, jadi $period akan tertimpa string 'Y-m'.
            'periodDate' => $period,
            'todayPosition' => $todayInAnchor ? $service->positionInMonth($today) : null,
            'isPast' => $anchor->endOfMonth()->lt($today),
            'totalOutstanding' => array_sum(array_column($items, 'outstanding')),
        ]);
    }
}
