<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $siteIds = DB::table('sites')
            ->where('slug', 'declair')
            ->pluck('id');

        if ($siteIds->isEmpty()) {
            return;
        }

        $pageIds = DB::table('site_pages')
            ->whereIn('site_id', $siteIds)
            ->pluck('id');

        if ($pageIds->isEmpty()) {
            return;
        }

        DB::table('site_sections')
            ->whereIn('site_page_id', $pageIds)
            ->delete();
    }

    public function down(): void
    {
        // The migration intentionally removes page content and cannot restore
        // the deleted section definitions without a backup of the database.
    }
};
