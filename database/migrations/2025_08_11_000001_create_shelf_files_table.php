<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shelf_files', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('path');
            $table->string('disk')->default('public');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable(); // bytes
            $table->string('hash')->nullable(); // for duplicate detection
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index(['hash']);
            $table->index(['user_id']);
            $table->index(['mime_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('shelf_files');
    }
};