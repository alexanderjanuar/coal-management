<x-filament-panels::page>
    <style>
        /* Completely remove all backgrounds, borders, and shadows from sections */
        .fi-section,
        .fi-section-content-ctn,
        .fi-section-content,
        .fi-section-header-ctn {
            background-color: transparent !important;
            box-shadow: none !important;
            border: none !important;
            border-radius: 0 !important;
        }
        
        /* Remove ring/outline */
        .fi-section {
            --tw-ring-shadow: none !important;
            --tw-ring-offset-shadow: none !important;
        }
        
        /* Add subtle spacing between sections */
        .fi-section + .fi-section {
            margin-top: 2rem;
        }
        
        /* Remove any padding that creates visual boxes */
        .fi-section-content-ctn {
            padding: 0 !important;
        }
    </style>

    <form wire:submit="save">
        {{ $this->form }}
        
        <div class="mt-6 flex justify-end gap-3">
            @foreach($this->getFormActions() as $action)
                {{ $action }}
            @endforeach
        </div>
    </form>
</x-filament-panels::page>