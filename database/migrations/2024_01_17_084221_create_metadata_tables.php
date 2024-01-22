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
        Schema::create('metadata', function (Blueprint $table) {
            $table->id();

            $table->string('og_type')->nullable();
            $table->string('og_image')->nullable();
            $table->string('card_type')->nullable();
            $table->boolean('noindex')->default(false);
            $table->boolean('nofollow')->default(false);
            $table->morphs('meta_describable');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('metadata_translations', function (Blueprint $table) {
            createDefaultTranslationsTableFields($table, 'metadata');

            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('canonical_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metadata_translations');
        Schema::dropIfExists('metadata');
    }
};
