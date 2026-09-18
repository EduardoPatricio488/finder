<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $siteIds = DB::table('sites')
            ->whereIn('slug', ['casa-co', 'casa-e-co'])
            ->pluck('id');

        if ($siteIds->isEmpty()) {
            return;
        }

        DB::table('sites')->whereIn('id', $siteIds)->delete();
    }

    public function down(): void
    {
        // O site removido era apenas conteúdo/demo legado e não é recriado.
    }
};
