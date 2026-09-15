<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_pages', function (Blueprint $table): void {
            if (! Schema::hasColumn('site_pages', 'theme')) {
                $table->json('theme')->nullable()->after('seo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_pages', function (Blueprint $table): void {
            if (Schema::hasColumn('site_pages', 'theme')) {
                $table->dropColumn('theme');
            }
        });
    }
};
