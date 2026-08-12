<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issuer_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('humanresource_id')->nullable()->constrained('humanresources')->nullOnDelete();
            $table->unsignedBigInteger('vacancy_id')->nullable()->index();
            $table->timestamp('start_at')->nullable()->index();
            $table->timestamp('finish_at')->nullable()->index();
            $table->unsignedInteger('duration')->nullable();
            $table->char('status', 1)->default('R');
            $table->string('comments')->nullable();
            $table->string('hash', 32)->unique();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
