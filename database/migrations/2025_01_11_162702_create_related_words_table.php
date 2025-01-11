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
        Schema::create('related_words', function (Blueprint $table) {
            $table->unsignedBigInteger('word_id');
            $table->unsignedBigInteger('related_word_id');

            $table->foreign('word_id')->references('id')->on('words')->onDelete('cascade');
            $table->foreign('related_word_id')->references('id')->on('words')->onDelete('cascade');

            $table->primary(['word_id', 'related_word_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('related_words');
    }
};
