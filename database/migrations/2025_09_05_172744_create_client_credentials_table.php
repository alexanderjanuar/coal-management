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
        Schema::create('client_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            
            // Core Tax credentials
            $table->string('core_tax_user_id')->nullable()->comment('Core Tax User ID');
            $table->string('core_tax_password')->nullable()->comment('Core Tax Password');
            
            // Email credentials
            $table->string('email')->nullable()->comment('Client email account');
            $table->string('email_password')->nullable()->comment('Client email password');
            
            // Additional credential types (untuk future expansion)
            $table->string('credential_type')->default('general')->comment('Type of credential: general, core_tax, email, etc');
            $table->text('notes')->nullable()->comment('Additional notes about credentials');
            
            // Security
            $table->timestamp('last_used_at')->nullable()->comment('Last time credentials were used');
            $table->boolean('is_active')->default(true)->comment('Whether credentials are active');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['client_id', 'credential_type']);
            $table->index(['client_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_credentials');
    }
};