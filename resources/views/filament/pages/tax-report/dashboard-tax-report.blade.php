<x-filament-panels::page>
    <div class="space-y-8">

        <!-- Filters Widget -->
        @livewire(\App\Livewire\TaxReport\Dashboard\Filters::class)

        <!-- Top Stats Overview -->
        @livewire(\App\Livewire\TaxReport\Dashboard\StatsOverview::class)

        <!-- Monthly Tax Chart & Tax Distribution -->
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <!-- Calendar - 2/3 width -->
            <div class="lg:col-span-2">
                @livewire('tax-report.dashboard.tax-calendar')
            </div>

            <!-- Top Unreported Clients - 1/3 width -->
            <div class="lg:col-span-1">
                @livewire(\App\Livewire\TaxReport\Dashboard\TopUnreportedClients::class)
            </div>
        </div>

        {{-- @livewire(\App\Livewire\TaxReport\TaxReportCountChart::class) --}}

        {{--

        <!-- Tax Calendar & Recent Reports -->
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <!-- Tax Calendar - 2/3 width -->
            <div class="lg:col-span-3">
                @livewire('tax-report.tax-calendar')
            </div>
        </div> --}}
    </div>
</x-filament-panels::page>