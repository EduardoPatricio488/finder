<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Plan;
use App\Models\Site;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Criar os Planos do Sistema
        $freePlan = Plan::create([
            'name' => 'Free',
            'price_monthly' => 0,
            'product_limit' => 5,
            'has_ai' => false,
            'has_reports' => false,
        ]);

        $proPlan = Plan::create([
            'name' => 'Pro',
            'price_monthly' => 2900, // €29.00
            'product_limit' => 100,
            'has_ai' => true,
            'has_reports' => true,
        ]);

        // 2. Criar o Utilizador Administrador (Tu)
        $admin = User::factory()->create([
            'name' => 'Eduardo Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'), // Altera isto depois
        ]);

        // 3. Criar uma Loja de Exemplo (Site) vinculada ao Plano Pro
        $site = Site::create([
            'name' => 'Casa & Co.',
            'slug' => 'casa-e-co',
            'owner_id' => $admin->id,
            'plan_id' => $proPlan->id, // Já começa com o plano pago
            'is_published' => true,
        ]);

        // 4. Vincular o utilizador à loja (tabela pivot se usares)
        // Se usares workspace_user ou site_user:
        $site->users()->attach($admin->id, ['role' => 'admin']);

        $this->command->info('Sistema SaaS inicializado com sucesso!');
        $this->command->info('Utilizador: admin@admin.com | Senha: password');
    }
}
