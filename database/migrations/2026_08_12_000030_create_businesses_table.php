<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description');
            $table->string('timezone');
            $table->string('strategy', 15)->default('timeslot');
            $table->string('plan', 20);
            $table->string('phone')->nullable();
            $table->string('postal_address')->nullable();
            $table->string('social_facebook')->nullable();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('domain_id')->nullable()->index();
            $table->boolean('listed')->default(false);
            $table->string('country_code', 2)->nullable()->index();
            $table->string('locale', 10)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('business_user', function (Blueprint $table) {
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['business_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_user');
        Schema::dropIfExists('businesses');
    }
};
