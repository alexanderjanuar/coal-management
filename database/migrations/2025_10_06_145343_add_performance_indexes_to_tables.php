<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Projects table indexes
        Schema::table('projects', function (Blueprint $table) {
            $table->index(['client_id', 'status'], 'projects_client_status_index');
            $table->index('status', 'projects_status_index');
        });

        // Clients table indexes
        Schema::table('clients', function (Blueprint $table) {
            $table->index('status', 'clients_status_index');
            $table->index('pic_id', 'clients_pic_index');
            $table->index('ar_id', 'clients_ar_index');
        });

        // Tax reports table indexes
        Schema::table('tax_reports', function (Blueprint $table) {
            $table->index(['client_id', 'month'], 'tax_reports_client_month_index');
            $table->index('ppn_report_status', 'tax_reports_ppn_status_index');
            $table->index('pph_report_status', 'tax_reports_pph_status_index');
            $table->index('bupot_report_status', 'tax_reports_bupot_status_index');
        });

        // Invoices table additional indexes
        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['tax_report_id', 'type'], 'invoices_report_type_index');
            $table->index('is_revision', 'invoices_is_revision_index');
            $table->index(['tax_report_id', 'is_revision'], 'invoices_report_revision_index');
        });

        // Income taxes table indexes
        Schema::table('income_taxes', function (Blueprint $table) {
            $table->index(['tax_report_id', 'employee_id'], 'income_taxes_report_employee_index');
        });

        // Bupots table indexes
        Schema::table('bupots', function (Blueprint $table) {
            $table->index(['tax_report_id', 'bupot_type'], 'bupots_report_type_index');
        });

        // Project steps indexes
        Schema::table('project_steps', function (Blueprint $table) {
            $table->index(['project_id', 'status'], 'project_steps_project_status_index');
        });

        // Required documents indexes
        Schema::table('required_documents', function (Blueprint $table) {
            $table->index(['project_step_id', 'status'], 'required_documents_step_status_index');
        });

        // Submitted documents indexes
        Schema::table('submitted_documents', function (Blueprint $table) {
            $table->index(['required_document_id', 'status'], 'submitted_documents_doc_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('projects_client_status_index');
            $table->dropIndex('projects_status_index');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex('clients_status_index');
            $table->dropIndex('clients_pic_index');
            $table->dropIndex('clients_ar_index');
        });

        Schema::table('tax_reports', function (Blueprint $table) {
            $table->dropIndex('tax_reports_client_month_index');
            $table->dropIndex('tax_reports_ppn_status_index');
            $table->dropIndex('tax_reports_pph_status_index');
            $table->dropIndex('tax_reports_bupot_status_index');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_report_type_index');
            $table->dropIndex('invoices_is_revision_index');
            $table->dropIndex('invoices_report_revision_index');
        });

        Schema::table('income_taxes', function (Blueprint $table) {
            $table->dropIndex('income_taxes_report_employee_index');
        });

        Schema::table('bupots', function (Blueprint $table) {
            $table->dropIndex('bupots_report_type_index');
        });

        Schema::table('project_steps', function (Blueprint $table) {
            $table->dropIndex('project_steps_project_status_index');
        });

        Schema::table('required_documents', function (Blueprint $table) {
            $table->dropIndex('required_documents_step_status_index');
        });

        Schema::table('submitted_documents', function (Blueprint $table) {
            $table->dropIndex('submitted_documents_doc_status_index');
        });
    }
};