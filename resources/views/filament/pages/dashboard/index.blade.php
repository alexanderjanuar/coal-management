<x-filament-panels::page class="w-full">
    <style>
        /* CSS Tambahan untuk Welcome Card */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes glow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
            }

            50% {
                box-shadow: 0 0 30px rgba(59, 130, 246, 0.5);
            }
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        .animate-glow {
            animation: glow 2s ease-in-out infinite;
        }

        .welcome-card {
            position: relative;
            background: #f0f9ff;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .dark .welcome-card {
            background: #1e293b;
        }

        .welcome-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }

        .dark .welcome-card:hover {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
        }

        /* Avatar hover effects */
        .avatar-container {
            transition: all 0.3s ease;
        }

        .avatar-container:hover {
            transform: scale(1.05);
        }

        /* Button hover effects */
        .action-button {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        /* Stats animation */
        .stat-item {
            transition: all 0.2s ease;
        }

        .stat-item:hover {
            transform: translateY(-2px);
        }

        /* Widget placeholder styles */
        .widget-placeholder {
            transition: all 0.3s ease;
        }

        .widget-placeholder:hover {
            transform: translateY(-2px);
        }

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

    {{-- Widget Grid Section --}}
    <div class="mt-6 space-y-6">
        {{-- First Row: 3 Widgets --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
            {{-- Widget Slot 1 - User Projects (Livewire) --}}
            <div class="widget-placeholder">
                @livewire('dashboard.widgets.user-projects-widget')
            </div>

            {{-- Widget Slot 2 - Daily Tasks (Livewire) --}}
            <div class="widget-placeholder">
                @livewire('dashboard.widgets.daily-tasks-widget')
            </div>

            {{-- Widget Slot 3 - Team Activity (Livewire) --}}
            <div class="widget-placeholder">
                @livewire('dashboard.widgets.team-activity-widget')
            </div>
        </div>

        {{-- Second Row: 3 Widgets --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
            {{-- Widget Slot 4 --}}
            <div class="widget-placeholder">
                @if(isset($widget_row_2_col_1))
                {!! $widget_row_2_col_1 !!}
                @else
                <x-dashboard.widget-placeholder title="Widget 2-1" description="Place your fourth widget here"
                    icon="heroicon-o-calendar" />
                @endif
            </div>

            {{-- Widget Slot 5 --}}
            <div class="widget-placeholder">
                @if(isset($widget_row_2_col_2))
                {!! $widget_row_2_col_2 !!}
                @else
                <x-dashboard.widget-placeholder title="Widget 2-2" description="Place your fifth widget here"
                    icon="heroicon-o-bell" />
                @endif
            </div>

            {{-- Widget Slot 6 --}}
            <div class="widget-placeholder">
                @if(isset($widget_row_2_col_3))
                {!! $widget_row_2_col_3 !!}
                @else
                <x-dashboard.widget-placeholder title="Widget 2-3" description="Place your sixth widget here"
                    icon="heroicon-o-cog-6-tooth" />
                @endif
            </div>
        </div>
    </div>

</x-filament-panels::page>