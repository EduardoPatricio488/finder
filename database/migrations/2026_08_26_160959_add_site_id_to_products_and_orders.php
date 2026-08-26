<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adiciona site_id em produtos
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'site_id')) {
                $table->foreignId('site_id')->nullable()->constrained()->cascadeOnDelete();
            }
        });

        // Adiciona site_id em ordens (pedidos), pois o erro mostra que também é usado lá
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'site_id')) {
                $table->foreignId('site_id')->nullable()->constrained()->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('site_id');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('site_id');
        });
    }
};
