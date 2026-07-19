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
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wall_id')->constrained()->cascadeOnDelete();
            $table->text('content');
            $table->string('author', 40)->nullable();
            $table->string('author_token', 64)->index();
            $table->string('color', 20)->default('jaune');
            $table->json('tags');
            $table->json('reactions');
            $table->boolean('pinned')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
