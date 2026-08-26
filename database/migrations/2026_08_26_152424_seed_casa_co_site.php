<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('sites')->insert([
            'name' => 'Casa & Co.',
            'slug' => 'casa-co',
            'tagline' => 'Coisas bonitas para viver melhor.',
            'description' => 'Uma seleção cuidada de artigos para a casa, para oferecer e para aproveitar os pequenos momentos do dia.',
            'category_label' => 'Loja',
            'accent' => 'amber',
            'primary_color' => '#f59e0b',
            'secondary_color' => '#1c1917',
            'status' => 'online',
            'type' => 'online_store',
            'subdomain' => 'casa-co',
            'homepage' => json_encode([
                'hero_title' => 'Coisas bonitas para viver melhor.',
                'hero_subtitle' => 'Uma seleção cuidada de artigos para a casa, para oferecer e para aproveitar os pequenos momentos do dia.',
                'primary_button_label' => 'Explorar produtos',
                'sections' => [
                    'hero' => true,
                    'featured_products' => true,
                    'about' => true,
                    'categories' => true,
                    'newsletter' => true,
                    'footer' => true,
                ],
            ]),
            'home_route' => 'sales',
            'is_published' => true,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('sites')->where('slug', 'casa-co')->delete();
    }
};
