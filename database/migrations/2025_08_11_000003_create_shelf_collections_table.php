<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shelf_collections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index(['user_id']);
            $table->index(['name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('shelf_collections');
    }
};