<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shelf_fileables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('shelf_files')->cascadeOnDelete();
            $table->morphs('fileable');
            $table->string('role')->nullable(); // featured, gallery, attachment, etc
            $table->integer('order')->nullable();
            $table->json('metadata')->nullable(); // alt text, captions, etc
            $table->timestamps();

            // Indexes
            $table->index(['fileable_type', 'fileable_id', 'role']);
            $table->index(['file_id']);
            $table->index(['order']);

            // Unique constraint to prevent duplicate file-model-role combinations
            $table->unique(['file_id', 'fileable_type', 'fileable_id', 'role']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('shelf_fileables');
    }
};