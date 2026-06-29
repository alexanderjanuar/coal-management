<div>
    {{-- Statistics Card — fokus PPh 21 (PPh 23 & 4(2) dipindahkan ke PPh Unifikasi) --}}
    <div class="mb-6 grid gap-4 sm:gap-6 sm:grid-cols-2">
        {{-- PPh 21 Card --}}
        <div class="rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 p-6 shadow-lg text-white">
            <div class="flex items-start justify-between mb-3">
                <span class="text-sm text-blue-100">PPh 21</span>
                <div class="rounded-lg bg-white/20 p-1.5 backdrop-blur-sm">
                    <x-filament::icon icon="heroicon-o-user-group" class="h-4 w-4 text-white" />
                </div>
            </div>
            <p class="text-2xl font-bold mb-2">
                Rp {{ number_format($pph21Total, 0, ',', '.') }}
            </p>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 text-xs text-blue-100">
                    <span class="w-2 h-2 rounded-full bg-white"></span>
                    {{ $pph21Count }} Bukti Potong
                </span>
            </div>
        </div>
    </div>

    {{-- Table Section --}}
    {{ $this->table }}
</div>