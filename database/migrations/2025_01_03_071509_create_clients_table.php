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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            
            $table->string('NPWP');
            $table->string('KPP')->nullable();
            $table->string('logo')->nullable();
            $table->enum('status',['Active','Inactive'])->default('Active');
            $table->string('EFIN')->nullable();
            $table->string('account_representative')->nullable();
            $table->string('person_in_charge')->nullable();
            $table->string('ar_phone_number')->nullable();
            $table->string('adress')->nullable();
            $table->string('email')->unique()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
