<x-filament-panels::page class="w-full">
    <style>
        .logo-container {
            width: 2.5rem;
            /* Smaller on mobile */
            height: 2.5rem;
            border-radius: 0.5rem;
            overflow: hidden;
            border: 2px solid rgb(var(--primary-50));
            transition: all 0.3s ease;
        }

        @media (min-width: 640px) {
            .logo-container {
                width: 3rem;
                height: 3rem;
                border-radius: 0.75rem;
            }
        }

        @media (min-width: 768px) {
            .logo-container {
                width: 4rem;
                height: 4rem;
            }
        }

        .logo-container:hover {
            border-color: rgb(var(--primary-100));
            transform: scale(1.02);
        }

        .logo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        /* Improved header container for better mobile layout */
        .header-container {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            padding: 0.75rem;
        }

        @media (min-width: 640px) {
            .header-container {
                padding: 1rem;
                gap: 1rem;
            }
        }

        @media (min-width: 768px) {
            .header-container {
                flex-direction: row;
                justify-content: space-between;
                align-items: flex-start;
                padding: 1.5rem;
            }
        }

        /* Improved content spacing for mobile */
        .content-container {
            padding: 0.75rem;
            margin-bottom: 1rem;
        }

        @media (min-width: 640px) {
            .content-container {
                padding: 1rem;
                margin-bottom: 1.5rem;
            }
        }

        @media (min-width: 768px) {
            .content-container {
                padding: 1.5rem;
                margin-bottom: 2rem;
            }
        }

        /* Status tabs scrolling for mobile */
        .status-tabs-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE and Edge */
        }

        .status-tabs-container::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari and Opera */
        }

        /* Responsive grid for project cards */
        .projects-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: 1fr;
        }

        @media (min-width: 640px) {
            .projects-grid {
                gap: 1.5rem;
            }
        }

        /* Project card responsive adjustments */
        .project-card {
            padding: 0.75rem;
            border-radius: 0.5rem;
        }

        @media (min-width: 640px) {
            .project-card {
                padding: 1rem;
                border-radius: 0.75rem;
            }
        }

        /* Responsive text sizes */
        .client-name {
            font-size: 1rem;
            line-height: 1.5rem;
        }

        @media (min-width: 640px) {
            .client-name {
                font-size: 1.125rem;
                line-height: 1.75rem;
            }
        }

        /* Project info responsive layout */
        .project-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        @media (min-width: 640px) {
            .project-info {
                flex-direction: row;
                align-items: center;
                gap: 1rem;
            }
        }

        /* Progress bar responsive sizing */
        .progress-container {
            width: 100%;
            max-width: 200px;
        }

        @media (min-width: 640px) {
            .progress-container {
                width: 150px;
            }
        }
    </style>
    {{-- Stats Overview --}}
    {{-- @livewire('dashboard.project-stats') --}}

    {{-- Status Tabs --}}
    
</x-filament-panels::page>