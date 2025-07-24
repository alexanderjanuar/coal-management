{{-- resources/views/livewire/user-detail/user-submitted-documents.blade.php --}}
<div>
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Dokumen yang Dikirim
        </h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Semua dokumen yang telah dikirim oleh {{ $user->name }}
        </p>
    </div>

    {{ $this->table }}
</div>