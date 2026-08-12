<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('nin')->nullable()->index();
            $table->string('email')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('mobile', 15)->nullable();
            $table->text('description')->nullable();
            $table->char('gender', 1);
            $table->string('occupation')->nullable();
            $table->string('martial_status')->nullable();
            $table->string('postal_address')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('business_contact', function (Blueprint $table) {
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->primary(['business_id', 'contact_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_contact');
        Schema::dropIfExists('contacts');
    }
};
