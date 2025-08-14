<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shelf_collection_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('shelf_collections')->cascadeOnDelete();
            $table->foreignId('file_id')->constrained('shelf_files')->cascadeOnDelete();
            $table->integer('order')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['collection_id', 'order']);
            $table->unique(['collection_id', 'file_id']); // Prevent duplicates in same collection
        });
    }

    public function down()
    {
        Schema::dropIfExists('shelf_collection_files');
    }
};