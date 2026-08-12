<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preferences', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('value');
            $table->enum('type', ['string', 'bool', 'int', 'float', 'array', 'time', 'json'])->default('string');
            $table->unsignedBigInteger('preferenceable_id');
            $table->string('preferenceable_type');
            $table->timestamps();

            $table->unique(['key', 'preferenceable_type', 'preferenceable_id'], 'preferences_unique');
            $table->index(['preferenceable_type', 'preferenceable_id'], 'preferences_preferenceable_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preferences');
    }
};
