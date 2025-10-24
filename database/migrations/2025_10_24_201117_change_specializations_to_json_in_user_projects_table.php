<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Convert existing data to JSON format
        $records = DB::table('user_projects')->whereNotNull('specializations')->get();
        
        foreach ($records as $record) {
            if ($record->specializations) {
                // Jika sudah JSON, skip
                $decoded = json_decode($record->specializations);
                if (json_last_error() === JSON_ERROR_NONE) {
                    continue;
                }
                
                // Convert text to JSON array
                // Jika berisi koma, split menjadi array
                if (strpos($record->specializations, ',') !== false) {
                    $items = array_map('trim', explode(',', $record->specializations));
                    $jsonValue = json_encode($items);
                } else {
                    // Jika single value, jadikan array dengan 1 elemen
                    $jsonValue = json_encode([$record->specializations]);
                }
                
                DB::table('user_projects')
                    ->where('id', $record->id)
                    ->update(['specializations' => $jsonValue]);
            }
        }
        
        // Step 2: Set NULL untuk empty strings
        DB::table('user_projects')
            ->where('specializations', '')
            ->update(['specializations' => null]);
        
        // Step 3: Change column type to JSON
        Schema::table('user_projects', function (Blueprint $table) {
            $table->json('specializations')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Convert JSON back to text
        $records = DB::table('user_projects')->whereNotNull('specializations')->get();
        
        foreach ($records as $record) {
            if ($record->specializations) {
                $decoded = json_decode($record->specializations, true);
                if (is_array($decoded)) {
                    $textValue = implode(', ', $decoded);
                    DB::table('user_projects')
                        ->where('id', $record->id)
                        ->update(['specializations' => $textValue]);
                }
            }
        }
        
        Schema::table('user_projects', function (Blueprint $table) {
            $table->text('specializations')->nullable()->change();
        });
    }
};