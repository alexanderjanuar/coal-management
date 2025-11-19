<x-filament-panels::page>
    <div class="space-y-8">

        <!-- Top Stats Overview -->
        @livewire(\App\Livewire\TaxReport\StatsOverview::class)


        {{-- <!-- Monthly Tax Chart & Tax Distribution -->
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <!-- Monthly Tax Chart - 2/3 width -->
            <div class="lg:col-span-2 overflow-hidden max-h-[800px]">
                @livewire(\App\Livewire\TaxReport\TaxReportCountChart::class)
            </div>

            <!-- Top Unreported Clients - 1/3 width -->
            <div class="overflow-hidden h-full">
                @livewire(\App\Livewire\TaxReport\TopUnreportedClients::class)
            </div>
        </div>


        <!-- Tax Calendar & Recent Reports -->
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <!-- Tax Calendar - 2/3 width -->
            <div class="lg:col-span-3">
                @livewire('tax-report.tax-calendar')
            </div>
        </div> --}}
    </div>
</x-filament-panels::page>