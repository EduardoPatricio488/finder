<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'role')) {
            DB::table('users')
                ->where('email', 'eduardopatricio06@sapo.pt')
                ->update(['role' => 'administrador']);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'role')) {
            DB::table('users')
                ->where('email', 'eduardopatricio06@sapo.pt')
                ->update(['role' => 'vendedor']);
        }
    }
};
