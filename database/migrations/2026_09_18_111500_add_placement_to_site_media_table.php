<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('site_media', 'placement')) {
            Schema::table('site_media', function (Blueprint $table): void {
                $table->string('placement', 40)->default('gallery')->after('alt_text')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('site_media', 'placement')) {
            Schema::table('site_media', function (Blueprint $table): void {
                $table->dropIndex(['placement']);
                $table->dropColumn('placement');
            });
        }
    }
};
