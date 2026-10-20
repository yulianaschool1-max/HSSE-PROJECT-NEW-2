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
        Schema::create('media_attachments', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('mediable'); // mediable_type & mediable_id
            $table->string('module', 50)->nullable()->index();
            $table->string('disk', 30)->default('public');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('file_name');
            $table->string('mime_type', 100);
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('original_size')->default(0);
            $table->unsignedBigInteger('compressed_size')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('uploaded_by')->nullable();
            $table->text('caption')->nullable();
            $table->timestamps();

            $table->index(['module', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_attachments');
    }
};
