<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\SubmittedDocument;
use Yaza\LaravelGoogleDriveStorage\Gdrive;
use Illuminate\Bus\Batchable;
class UploadToGoogleDrive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $submission;
    protected $localPath;
    protected $drivePath;

    /**
     * Create a new job instance.
     */
    public function __construct(SubmittedDocument $submission, string $localPath, string $drivePath)
    {
        $this->submission = $submission;
        $this->localPath = $localPath;
        $this->drivePath = $drivePath;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Upload file to Google Drive
            Gdrive::put($this->drivePath, $this->localPath);
        } catch (\Exception $e) {
            report($e);
            throw $e;
        }
    }
}
