<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\Project;
use Filament\Resources\Pages\Page;
use Spatie\Activitylog\Models\Activity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ViewProjectActivity extends Page
{
    protected static string $resource = ProjectResource::class;
    protected static string $view = 'filament.resources.project-resource.pages.view-project-activity';
    protected static ?string $title = 'Project Activity Log';
    protected static ?string $navigationIcon = 'heroicon-o-clock';

    public $record;
    public $activities;

    public function mount(Project $record): void
    {
        $this->record = $record;
        $this->loadActivities();
    }

    private function loadActivities()
    {
        // Get direct activities for the project
        $projectActivities = Activity::where('subject_type', get_class($this->record))
            ->where('subject_id', $this->record->id)
            ->get();

        // Get activities for project steps
        $stepIds = $this->record->steps->pluck('id')->toArray();
        $stepActivities = Activity::where('subject_type', 'App\\Models\\ProjectStep')
            ->whereIn('subject_id', $stepIds)
            ->get();

        // Get activities for tasks
        $taskIds = \App\Models\Task::whereIn('project_step_id', $stepIds)->pluck('id')->toArray();
        $taskActivities = Activity::where('subject_type', 'App\\Models\\Task')
            ->whereIn('subject_id', $taskIds)
            ->get();

        // Get activities for required documents
        $docIds = \App\Models\RequiredDocument::whereIn('project_step_id', $stepIds)->pluck('id')->toArray();
        $docActivities = Activity::where('subject_type', 'App\\Models\\RequiredDocument')
            ->whereIn('subject_id', $docIds)
            ->get();

        // Get activities for submitted documents
        $subDocActivities = Activity::where('subject_type', 'App\\Models\\SubmittedDocument')
            ->whereIn('subject_id', function ($query) use ($docIds) {
                $query->select('id')
                    ->from('submitted_documents')
                    ->whereIn('required_document_id', $docIds);
            })
            ->get();

        // Merge all activities and sort by creation date
        $this->activities = $projectActivities
            ->merge($stepActivities)
            ->merge($taskActivities)
            ->merge($docActivities)
            ->merge($subDocActivities)
            ->sortByDesc('created_at');
    }

    public function generateProjectReport()
    {
        $fileName = $this->record->name . '-Project-Report-' . now()->format('Y-m-d') . '.pdf';

        // Load project steps with relationships
        $steps = $this->record->steps()->orderBy('order')->with(['tasks', 'requiredDocuments.submittedDocuments'])->get();

        // Get all required documents
        $requiredDocuments = \App\Models\RequiredDocument::whereIn(
            'project_step_id',
            $steps->pluck('id')->toArray()
        )->get();

        // Generate PDF
        $pdf = PDF::loadView('pdf.project-report', [
            'project' => $this->record,
            'steps' => $steps,
            'activities' => $this->activities,
            'requiredDocuments' => $requiredDocuments, 
        ]);

        $pdf->setPaper('a4', 'portrait');

        // Store PDF temporarily
        Storage::disk('public')->put('temp/' . $fileName, $pdf->output());

        // Return file URL for download
        return Storage::disk('public')->url('temp/' . $fileName);
    }
}