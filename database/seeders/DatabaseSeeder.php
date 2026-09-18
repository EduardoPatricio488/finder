<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $freePlan = Plan::create([
            'name' => 'Free',
            'price_monthly' => 0,
            'product_limit' => 5,
            'has_ai' => false,
            'has_reports' => false,
        ]);

        $proPlan = Plan::create([
            'name' => 'Pro',
            'price_monthly' => 2900,
            'product_limit' => 100,
            'has_ai' => true,
            'has_reports' => true,
        ]);

        $admin = User::factory()->create([
            'name' => 'Eduardo Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
            'role' => 'administrador',
        ]);

        $this->command->info('Sistema SaaS inicializado com sucesso!');
        $this->command->info('Utilizador: admin@admin.com | Senha: password');
    }
}
