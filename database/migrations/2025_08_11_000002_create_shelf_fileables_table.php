<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    public function up(): void {
        Schema::create('shelf_fileables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('shelf_files')->cascadeOnDelete();
            $table->morphs('fileable'); // polymorphic attachment
            $table->string('role')->nullable(); // featured, gallery, attachment
            $table->integer('order')->nullable();
            $table->json('metadata')->nullable(); // alt text, captions, etc
            $table->timestamps();

            $table->index(['fileable_type', 'fileable_id', 'role']);
            $table->index(['file_id']);
            $table->index(['order']);
            $table->unique(['file_id', 'fileable_type', 'fileable_id', 'role']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('shelf_fileables');
    }
};