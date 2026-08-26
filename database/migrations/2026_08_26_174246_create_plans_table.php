<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
    Schema::create('plans', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Ex: Free, Pro, Business
        $table->integer('price_monthly'); // Preço em cêntimos (ex: 2900 para €29.00)
        $table->integer('product_limit'); // Limite de produtos (ex: 10)
        $table->boolean('has_ai')->default(false); // Acesso ao Assistente IA
        $table->boolean('has_reports')->default(false); // Acesso aos Relatórios
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
