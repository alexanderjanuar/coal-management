<x-filament-panels::page class="w-full">
    <style>
        /* CSS Tambahan untuk Welcome Card */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        @keyframes glow {
            0%, 100% { box-shadow: 0 0 20px rgba(59, 130, 246, 0.3); }
            50% { box-shadow: 0 0 30px rgba(59, 130, 246, 0.5); }
        }

        .animate-float { animation: float 3s ease-in-out infinite; }
        .animate-glow { animation: glow 2s ease-in-out infinite; }

        .welcome-card {
            position: relative;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #f3e8ff 100%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .dark .welcome-card {
            background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #374151 100%);
        }

        .welcome-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }

        .dark .welcome-card:hover {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
        }

        /* Avatar hover effects */
        .avatar-container { transition: all 0.3s ease; }
        .avatar-container:hover { transform: scale(1.05); }

        /* Button hover effects */
        .action-button {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .action-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .action-button:hover::before { left: 100%; }

        /* Stats animation */
        .stat-item { transition: all 0.2s ease; }
        .stat-item:hover { transform: translateY(-2px); }

        /* Responsive improvements */
        @media (max-width: 640px) {
            .welcome-card {
                margin: 0 -1rem;
                border-radius: 0;
            }
        }
    </style>

    {{-- Stats Overview --}}
    @livewire('dashboard.widget.project-stats-overview')

    {{-- Greeting Card --}}
    @livewire('dashboard.components.greeting-card')

    {{-- First Row: PIC Chart and Overdue Projects --}}
    {{-- <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <div>
            @livewire('widget.person-in-charge-project-chart')
        </div>
        <div>
            @livewire('dashboard.overdue-projects')
        </div>
    </div> --}}

    {{-- Second Row: Recent Activity Table (Full Width) --}}
    {{-- <div class="mb-4">
        @livewire('widget.recent-activity-table')
    </div> --}}

    {{-- Third Row: Project Report Chart and Properties Chart --}}
    {{-- <div class="grid grid-cols-1 lg:grid-cols-6 gap-4">
        <div class="lg:col-span-4">
            @livewire('widget.project-report-chart')
        </div>
        <div class="lg:col-span-2">
            @livewire('widget.project-properties-chart')
        </div>
    </div> --}}

</x-filament-panels::page>