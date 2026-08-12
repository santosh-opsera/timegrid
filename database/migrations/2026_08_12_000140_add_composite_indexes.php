<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreign('vacancy_id')
                ->references('id')
                ->on('vacancies')
                ->nullOnDelete();

            $table->index(['business_id', 'start_at', 'status'], 'appointments_business_start_status_index');
            $table->index(['contact_id', 'status'], 'appointments_contact_status_index');
        });

        Schema::table('vacancies', function (Blueprint $table) {
            $table->foreign('humanresource_id')
                ->references('id')
                ->on('humanresources')
                ->nullOnDelete();

            $table->index(['business_id', 'date', 'service_id'], 'vacancies_business_date_service_index');
        });

        Schema::table('business_contact', function (Blueprint $table) {
            $table->index(['business_id', 'contact_id'], 'business_contact_business_contact_index');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->index('email', 'contacts_email_index');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex('contacts_email_index');
        });

        Schema::table('business_contact', function (Blueprint $table) {
            $table->dropIndex('business_contact_business_contact_index');
        });

        Schema::table('vacancies', function (Blueprint $table) {
            $table->dropForeign(['humanresource_id']);
            $table->dropIndex('vacancies_business_date_service_index');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['vacancy_id']);
            $table->dropIndex('appointments_business_start_status_index');
            $table->dropIndex('appointments_contact_status_index');
        });
    }
};
