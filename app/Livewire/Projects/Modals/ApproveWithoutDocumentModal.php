<?php

namespace App\Livewire\Projects\Modals;

use Livewire\Component;
use App\Models\RequiredDocument;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;

class ApproveWithoutDocumentModal extends Component implements HasForms
{
    use InteractsWithForms;

    public RequiredDocument $document;
    public ?array $data = [];

    public function mount(RequiredDocument $document): void
    {
        $this->document = $document;
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('description')
                    ->label('Alasan Approval Tanpa Dokumen')
                    ->placeholder('Jelaskan mengapa dokumen ini disetujui tanpa upload dokumen...')
                    ->required()
                    ->rows(4)
                    ->maxLength(1000)
                    ->helperText('Berikan penjelasan yang jelas mengenai alasan persetujuan tanpa dokumen.')
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        
        try {
            // Update document status dan description
            $oldStatus = $this->document->status;
            $this->document->update([
                'status' => 'approved_without_document',
                'description' => $data['description']
            ]);

            // Reset form
            $this->form->fill();

            // Close modal PERTAMA
            $this->dispatch('close-modal', ['id' => 'approve-without-document-' . $this->document->id]);

            // TUNGGU sebentar sebelum dispatch event lain
            $this->dispatch('documentApprovedWithoutUpload', [
                'documentId' => $this->document->id,
            ]);

            // HANYA satu notification
            Notification::make()
                ->title('Document Approved')
                ->body('Document has been approved without requiring file upload.')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to approve document. Please try again.')
                ->danger()
                ->send();
        }
    }
    public function render()
    {
        return view('livewire.projects.modals.approve-without-document-modal');
    }
}
