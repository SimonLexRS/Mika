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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Datos de la transacción
            $table->decimal('amount', 15, 2);
            $table->string('type'); // income, expense
            $table->string('category');
            $table->date('transaction_date');
            $table->text('description')->nullable();

            // Archivos adjuntos
            $table->string('receipt_image_path')->nullable();

            // Estado
            $table->string('status')->default('approved'); // pending, approved

            // Metadata adicional
            $table->json('meta_data')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices para consultas frecuentes
            $table->index(['user_id', 'transaction_date']);
            $table->index(['user_id', 'type', 'category']);
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'type', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
