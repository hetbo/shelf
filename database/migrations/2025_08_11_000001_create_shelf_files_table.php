<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('shelf_files', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('path');
            $table->string('disk')->default('public');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('hash')->nullable(); // duplicate detection
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('folder_id')->nullable()->constrained('shelf_folders')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['hash']);
            $table->index(['user_id']);
            $table->index(['mime_type']);
            $table->index(['folder_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('shelf_files');
    }
};