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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();

            // Contenido del mensaje
            $table->text('content');
            $table->string('sender'); // user, bot
            $table->string('type')->default('text'); // text, card, image, quick_replies, form

            // Metadata para renderizado especial
            $table->json('meta_data')->nullable();

            $table->timestamps();

            // Índices
            $table->index(['conversation_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
