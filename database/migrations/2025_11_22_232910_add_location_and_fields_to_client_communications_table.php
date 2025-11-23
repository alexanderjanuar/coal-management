<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('client_communications', function (Blueprint $table) {
            // Location information
            $table->string('location')->nullable()->after('communication_time')
                  ->comment('Lokasi pertemuan/komunikasi (alamat, nama tempat, atau platform online)');
            
            // Coordinate location (latitude, longitude)
            $table->decimal('latitude', 10, 8)->nullable()->after('location')
                  ->comment('Latitude koordinat lokasi');
            
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude')
                  ->comment('Longitude koordinat lokasi');
            
            // Participants - JSON array untuk menyimpan peserta dari sisi client
            $table->json('client_participants')->nullable()->after('longitude')
                  ->comment('Daftar peserta dari pihak client (bisa reference ke client_contacts atau manual input)');
            
            // Internal participants - JSON array untuk menyimpan peserta dari internal team
            $table->json('internal_participants')->nullable()->after('client_participants')
                  ->comment('Daftar peserta dari internal team (user_ids atau manual input)');
            
            // Status tracking
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'rescheduled'])
                  ->default('completed')->after('internal_participants')
                  ->comment('Status komunikasi');
            
            // Outcome/result
            $table->text('outcome')->nullable()->after('status')
                  ->comment('Hasil atau kesimpulan dari komunikasi');
            
            // Priority level
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])
                  ->default('normal')->after('outcome')
                  ->comment('Tingkat prioritas komunikasi');
            
            // Related project
            $table->foreignId('project_id')->nullable()->after('priority')
                  ->constrained()->nullOnDelete()
                  ->comment('Project terkait (jika ada)');
            
            // Communication method details (untuk tipe tertentu)
            $table->string('meeting_link')->nullable()->after('project_id')
                  ->comment('Link untuk video call atau online meeting');
            
            $table->string('meeting_platform')->nullable()->after('meeting_link')
                  ->comment('Platform yang digunakan (Zoom, Google Meet, Teams, dll)');
        });

        // Add indexes for better performance
        Schema::table('client_communications', function (Blueprint $table) {
            $table->index(['client_id', 'status'], 'client_comms_client_status_idx');
            $table->index(['project_id', 'communication_date'], 'client_comms_project_date_idx');
            $table->index('status', 'client_comms_status_idx');
            $table->index('priority', 'client_comms_priority_idx');
            $table->index(['latitude', 'longitude'], 'client_comms_coordinates_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_communications', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('client_comms_client_status_idx');
            $table->dropIndex('client_comms_project_date_idx');
            $table->dropIndex('client_comms_status_idx');
            $table->dropIndex('client_comms_priority_idx');
            $table->dropIndex('client_comms_coordinates_idx');
            
            // Drop foreign keys
            $table->dropForeign(['project_id']);
            
            // Drop columns
            $table->dropColumn([
                'location',
                'latitude',
                'longitude',
                'client_participants',
                'internal_participants',
                'status',
                'outcome',
                'priority',
                'project_id',
                'meeting_link',
                'meeting_platform',
            ]);
        });
    }
};