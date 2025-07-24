{{-- resources/views/filament/pages/user-client-details.blade.php --}}
<x-filament-panels::page x-data="{ tab: 'tab1' }">
    
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ $this->getRecord()->user->name ?? 'Detail Pengguna' }}
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Kelola informasi pengguna, dokumen, dan penugasan proyek
        </p>
    </div>

    {{-- Tabs Navigation --}}
    <x-filament::tabs label="Tab Detail Pengguna" class="mb-6">
        <x-filament::tabs.item 
            icon="heroicon-o-document-text"
            @click="tab = 'tab1'" 
            :alpine-active="'tab === \'tab1\''"
        >
            Dokumen yang Dikirim
            @if($this->getRecord()->user)
                <x-slot name="badge">
                    {{ $this->getRecord()->user->submittedDocuments->count() }}
                </x-slot>
            @endif
        </x-filament::tabs.item>

        <x-filament::tabs.item 
            icon="heroicon-o-briefcase"
            @click="tab = 'tab2'" 
            :alpine-active="'tab === \'tab2\''"
        >
            Penugasan Proyek
            @if($this->getRecord()->user)
                <x-slot name="badge">
                    {{ $this->getRecord()->user->userProjects->count() }}
                </x-slot>
            @endif
        </x-filament::tabs.item>

        <x-filament::tabs.item 
            icon="heroicon-o-user-circle"
            @click="tab = 'tab3'" 
            :alpine-active="'tab === \'tab3\''"
        >
            Profil Pengguna
        </x-filament::tabs.item>
    </x-filament::tabs>

    {{-- Tab Contents --}}
    <div>
        {{-- Tab 1: Submitted Documents --}}
        <div x-show="tab === 'tab1'" x-transition:enter.duration.300ms>
            @if($this->getRecord()->user)
                @livewire('user-detail.user-submitted-documents', ['user' => $this->getRecord()->user])
            @else
                <x-filament::section>
                    <div class="text-center py-8">
                        <x-heroicon-o-exclamation-triangle class="w-12 h-12 mx-auto text-gray-400 mb-4" />
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                            Pengguna Tidak Ditemukan
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Tidak dapat memuat informasi pengguna.
                        </p>
                    </div>
                </x-filament::section>
            @endif
        </div>

        {{-- Tab 2: Project Assignments --}}
        <div x-show="tab === 'tab2'" x-transition:enter.duration.300ms>
            <x-filament::section>
                <x-slot name="heading">
                    Penugasan Proyek
                </x-slot>
                
                <x-slot name="description">
                    Lihat dan kelola penugasan proyek serta peran pengguna
                </x-slot>

                <div class="space-y-4">
                    @if($this->getRecord()->user && $this->getRecord()->user->userProjects->count() > 0)
                        @foreach($this->getRecord()->user->userProjects as $userProject)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900 dark:text-white">
                                            {{ $userProject->project->name }}
                                        </h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            Klien: {{ $userProject->project->client->name }}
                                        </p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            Tenggat: {{ $userProject->project->due_date->format('d M Y') }}
                                        </p>
                                    </div>
                                    <div class="flex flex-col items-end space-y-2">
                                        <x-filament::badge 
                                            :color="match($userProject->role) {
                                                'direktur' => 'danger',
                                                'person-in-charge' => 'warning',
                                                'staff' => 'primary',
                                                default => 'gray'
                                            }"
                                        >
                                            {{ match($userProject->role) {
                                                'direktur' => 'Direktur',
                                                'person-in-charge' => 'Penanggung Jawab',
                                                'staff' => 'Staff',
                                                default => ucfirst($userProject->role)
                                            } }}
                                        </x-filament::badge>
                                        <x-filament::badge 
                                            :color="match($userProject->project->status) {
                                                'completed' => 'success',
                                                'in_progress' => 'warning',
                                                'draft' => 'gray',
                                                'canceled' => 'danger',
                                                'analysis' => 'info',
                                                'review' => 'purple',
                                                'completed (Not Payed Yet)' => 'orange',
                                                default => 'primary'
                                            }"
                                        >
                                            {{ match($userProject->project->status) {
                                                'draft' => 'Draft',
                                                'analysis' => 'Analisis',
                                                'in_progress' => 'Sedang Berjalan',
                                                'completed' => 'Selesai',
                                                'review' => 'Review',
                                                'completed (Not Payed Yet)' => 'Selesai (Belum Dibayar)',
                                                'canceled' => 'Dibatalkan',
                                                default => ucfirst($userProject->project->status)
                                            } }}
                                        </x-filament::badge>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-8">
                            <x-heroicon-o-briefcase class="w-12 h-12 mx-auto text-gray-400 mb-4" />
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                Tidak Ada Penugasan Proyek
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Pengguna ini belum ditugaskan ke proyek manapun.
                            </p>
                        </div>
                    @endif
                </div>
            </x-filament::section>
        </div>

        {{-- Tab 3: User Profile --}}
        <div x-show="tab === 'tab3'">
            <x-filament::section>
                <x-slot name="heading">
                    Informasi Profil Pengguna
                </x-slot>
                
                <x-slot name="description">
                    Informasi dasar akun pengguna dan pengaturan
                </x-slot>

                @if($this->getRecord()->user)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Nama:</span>
                                <p class="text-sm text-gray-900 dark:text-white">{{ $this->getRecord()->user->name }}</p>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Email:</span>
                                <p class="text-sm text-gray-900 dark:text-white">{{ $this->getRecord()->user->email }}</p>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Email Terverifikasi:</span>
                                <x-filament::badge 
                                    :color="$this->getRecord()->user->email_verified_at ? 'success' : 'danger'"
                                >
                                    {{ $this->getRecord()->user->email_verified_at ? 'Terverifikasi' : 'Belum Terverifikasi' }}
                                </x-filament::badge>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Akun Dibuat:</span>
                                <p class="text-sm text-gray-900 dark:text-white">
                                    {{ $this->getRecord()->user->created_at->format('d M Y H:i') }}
                                </p>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Terakhir Diperbarui:</span>
                                <p class="text-sm text-gray-900 dark:text-white">
                                    {{ $this->getRecord()->user->updated_at->format('d M Y H:i') }}
                                </p>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Proyek:</span>
                                <p class="text-sm text-gray-900 dark:text-white">
                                    {{ $this->getRecord()->user->userProjects->count() }}
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-heroicon-o-user-circle class="w-12 h-12 mx-auto text-gray-400 mb-4" />
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                            Informasi Pengguna Tidak Tersedia
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Tidak dapat memuat informasi profil pengguna.
                        </p>
                    </div>
                @endif
            </x-filament::section>
        </div>
    </div>

</x-filament-panels::page>