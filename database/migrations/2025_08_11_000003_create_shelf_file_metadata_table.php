<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('shelf_file_metadata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('shelf_files')->onDelete('cascade');
            $table->string('key');
            $table->text('value');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('shelf_file_metadata');
    }
};