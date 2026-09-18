<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'categories',
            'products',
            'customers',
            'orders',
            'payments',
            'stock_movements',
            'store_configs',
            'promotions',
            'product_reviews',
            'coupons',
            'suppliers',
            'goals',
            'activity_logs',
            'customer_addresses',
            'customer_payment_methods',
            'customer_notification_preferences',
        ];

        foreach ($tables as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'site_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->foreignId('site_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            });
        }

    }

    public function down(): void
    {
        $tables = [
            'customer_notification_preferences',
            'customer_payment_methods',
            'customer_addresses',
            'activity_logs',
            'goals',
            'suppliers',
            'coupons',
            'promotions',
            'product_reviews',
            'store_configs',
            'stock_movements',
            'payments',
            'orders',
            'customers',
            'products',
            'categories',
        ];

        foreach ($tables as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'site_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropConstrainedForeignId('site_id');
            });
        }
    }
};
