<x-filament-panels::page>
    <div class="space-y-6" x-data="{ 
            activeTab: @entangle('activeTab'),
            previousTab: 'overview',
            isTransitioning: false,
            changeTab(tab) {
                if (this.activeTab === tab) return;
                this.previousTab = this.activeTab;
                this.isTransitioning = true;
                setTimeout(() => {
                    this.activeTab = tab;
                    $wire.setActiveTab(tab);
                    setTimeout(() => { this.isTransitioning = false; }, 300);
                }, 50);
            },
            updateIndicator() {
                const indicator = this.$refs.indicator;
                const activeButton = this.$refs[this.activeTab];
                if (indicator && activeButton) {
                    indicator.style.left = activeButton.offsetLeft + 'px';
                    indicator.style.width = activeButton.offsetWidth + 'px';
                }
            }
        }" x-init="
            $nextTick(() => {
                updateIndicator();
            });
            
            $watch('activeTab', () => {
                $nextTick(() => {
                    updateIndicator();
                });
            });
            
            // Update on window resize
            window.addEventListener('resize', () => {
                updateIndicator();
            });
        ">
        {{-- Tab Navigation --}}
        <div class="relative border-b border-gray-200 dark:border-gray-700">
            {{-- Active Tab Indicator (sliding underline) --}}
            <div x-ref="indicator"
                class="absolute bottom-0 left-0 h-0.5 bg-primary-600 dark:bg-primary-500 transition-all duration-300 ease-in-out"
                style="will-change: left, width;"></div>

            <nav class="-mb-px flex gap-4 overflow-x-auto" aria-label="Tabs">
                {{-- Overview Tab --}}
                <button @click="changeTab('overview')" x-ref="overview" type="button"
                    class="group relative flex items-center gap-2 whitespace-nowrap px-1 py-4 text-sm font-medium transition-colors duration-200 focus:outline-none"
                    :class="{
                        'text-primary-600 dark:text-primary-400 font-semibold': activeTab === 'overview',
                        'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300': activeTab !== 'overview'
                    }">
                    <x-heroicon-o-home class="h-5 w-5 transition-transform duration-200 group-hover:scale-110"
                        ::class="{ 'text-primary-600 dark:text-primary-400': activeTab === 'overview' }" />
                    <span>Ringkasan</span>
                </button>

                {{-- Projects Tab --}}
                <button @click="changeTab('projects')" x-ref="projects" type="button"
                    class="group relative flex items-center gap-2 whitespace-nowrap px-1 py-4 text-sm font-medium transition-colors duration-200 focus:outline-none"
                    :class="{
                        'text-primary-600 dark:text-primary-400 font-semibold': activeTab === 'projects',
                        'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300': activeTab !== 'projects'
                    }">
                    <x-heroicon-o-folder class="h-5 w-5 transition-transform duration-200 group-hover:scale-110"
                        ::class="{ 'text-primary-600 dark:text-primary-400': activeTab === 'projects' }" />
                    <span>Proyek</span>
                </button>

                {{-- Documents Tab --}}
                <button @click="changeTab('documents')" x-ref="documents" type="button"
                    class="group relative flex items-center gap-2 whitespace-nowrap px-1 py-4 text-sm font-medium transition-colors duration-200 focus:outline-none"
                    :class="{
                        'text-primary-600 dark:text-primary-400 font-semibold': activeTab === 'documents',
                        'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300': activeTab !== 'documents'
                    }">
                    <x-heroicon-o-document-text class="h-5 w-5 transition-transform duration-200 group-hover:scale-110"
                        ::class="{ 'text-primary-600 dark:text-primary-400': activeTab === 'documents' }" />
                    <span>Dokumen</span>
                </button>

                {{-- Tax Reports Tab --}}
                <button @click="changeTab('tax-reports')" x-ref="tax-reports" type="button"
                    class="group relative flex items-center gap-2 whitespace-nowrap px-1 py-4 text-sm font-medium transition-colors duration-200 focus:outline-none"
                    :class="{
                        'text-primary-600 dark:text-primary-400 font-semibold': activeTab === 'tax-reports',
                        'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300': activeTab !== 'tax-reports'
                    }">
                    <x-heroicon-o-document-chart-bar
                        class="h-5 w-5 transition-transform duration-200 group-hover:scale-110"
                        ::class="{ 'text-primary-600 dark:text-primary-400': activeTab === 'tax-reports' }" />
                    <span>Laporan Pajak</span>
                </button>

                {{-- Profile Tab --}}
                <button @click="changeTab('profile')" x-ref="profile" type="button"
                    class="group relative flex items-center gap-2 whitespace-nowrap px-1 py-4 text-sm font-medium transition-colors duration-200 focus:outline-none"
                    :class="{
                        'text-primary-600 dark:text-primary-400 font-semibold': activeTab === 'profile',
                        'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300': activeTab !== 'profile'
                    }">
                    <x-heroicon-o-user-circle
                        class="h-5 w-5 transition-transform duration-200 group-hover:scale-110"
                        ::class="{ 'text-primary-600 dark:text-primary-400': activeTab === 'profile' }" />
                    <span>Profil</span>
                </button>
            </nav>
        </div>

        {{-- Tab Content with Transitions --}}
        <div class="relative mt-6">
            {{-- Overview Tab Content --}}
            <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-4">
                @livewire('client.panel.overview-tab')
            </div>

            {{-- Projects Tab Content --}}
            <div x-show="activeTab === 'projects'" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-4">
                @livewire('client.panel.proyek-tab')
            </div>

            {{-- Documents Tab Content --}}
            <div x-show="activeTab === 'documents'" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-4">
                @livewire('client.panel.document-tab')
            </div>

            {{-- Tax Reports Tab Content --}}
            <div x-show="activeTab === 'tax-reports'" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-4">
                @livewire('client.panel.tax-report-tab')
            </div>

            {{-- Profile Tab Content --}}
            <div x-show="activeTab === 'profile'" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-4">
                @livewire('client.panel.profile-tab')
            </div>

            {{-- Loading Overlay --}}
            <div x-show="isTransitioning"
                class="absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-gray-900/50"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="flex items-center gap-2 rounded-lg bg-white px-4 py-2 shadow-lg dark:bg-gray-800">
                    <svg class="h-5 w-5 animate-spin text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Memuat...</span>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>