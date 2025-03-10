<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;

class StorageDownloadController extends Controller
{
    public function downloadAll()
    {
        try {
            // Check if user has permission
            if (!auth()->user()->hasRole('super-admin')) {
                abort(403, 'Unauthorized action.');
            }

            // Create temporary directory if it doesn't exist
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Create ZIP filename with timestamp
            $zipFileName = sprintf('clients_backup_%s.zip', now()->format('Y-m-d_His'));
            $zipPath = $tempDir . '/' . $zipFileName;

            // Create new ZIP archive
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('Cannot create zip file');
            }

            // Get all files from storage/app/public/clients directory
            $files = Storage::disk('public')->allFiles('clients');

            // Add each file to the ZIP with proper folder naming
            foreach ($files as $file) {
                $filePath = storage_path('app/public/' . $file);
                if (file_exists($filePath)) {
                    // Get the path parts
                    $pathParts = explode('/', $file);
                    
                    // Format the client and project folder names if they exist
                    if (count($pathParts) > 1) {
                        $pathParts[1] = strtoupper(str_replace('-', ' ', $pathParts[1])); // Client name
                        if (isset($pathParts[2])) {
                            $pathParts[2] = strtoupper(str_replace('-', ' ', $pathParts[2])); // Project name
                        }
                    }
                    
                    // Reconstruct the path with formatted folder names
                    $formattedPath = implode('/', $pathParts);
                    
                    // Add to ZIP with formatted path
                    $zip->addFile($filePath, $formattedPath);
                }
            }

            $zip->close();

            // Return the ZIP file for download and delete it afterward
            return response()->download($zipPath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            report($e);
            
            Notification::make()
                ->title('Download Failed')
                ->body('Failed to create clients backup. Please try again.')
                ->danger()
                ->send();

            return redirect()->back();
        }
    }
}
