<?php

declare(strict_types=1);

namespace App\Support\WebsiteBuilder;

final class WebsiteTemplates
{
    /** @return array<string, array{name:string, description:string, pages:list<array{name:string,slug:string,sections:list<string>}>}> */
    public static function all(): array
    {
        return [
            'business' => self::template('Negócio', 'Website profissional para empresas e serviços.', [
                ['Home', 'home', ['hero', 'feature_grid', 'testimonials', 'cta']],
                ['Sobre nós', 'sobre-nos', ['hero', 'text', 'feature_grid']],
                ['Serviços', 'servicos', ['hero', 'feature_grid', 'faq', 'cta']],
                ['Contactos', 'contactos', ['hero', 'contact_form', 'map']],
            ]),
            'restaurant' => self::template('Restaurante', 'Presença digital para restaurantes, cafés e espaços de restauração.', [
                ['Home', 'home', ['hero', 'image', 'feature_grid', 'cta']],
                ['Menu', 'menu', ['hero', 'product_grid', 'cta']],
                ['Sobre', 'sobre', ['hero', 'text', 'gallery']],
                ['Contactos', 'contactos', ['hero', 'contact_form', 'map']],
            ]),
            'portfolio' => self::template('Portfólio', 'Apresenta projetos, trabalho e experiência.', [
                ['Home', 'home', ['hero', 'gallery', 'cta']],
                ['Projetos', 'projetos', ['hero', 'gallery', 'feature_grid']],
                ['Sobre mim', 'sobre', ['hero', 'text', 'testimonials']],
                ['Contactos', 'contactos', ['hero', 'contact_form']],
            ]),
            'freelancer' => self::template('Freelancer', 'Website simples e profissional para profissionais independentes.', [
                ['Home', 'home', ['hero', 'feature_grid', 'testimonials', 'cta']],
                ['Serviços', 'servicos', ['hero', 'feature_grid', 'pricing']],
                ['Sobre mim', 'sobre', ['hero', 'text']],
                ['Contactos', 'contactos', ['hero', 'contact_form']],
            ]),
            'agency' => self::template('Agência', 'Website comercial para agências criativas, digitais e consultoras.', [
                ['Home', 'home', ['hero', 'feature_grid', 'testimonials', 'cta']],
                ['Serviços', 'servicos', ['hero', 'feature_grid', 'pricing']],
                ['Casos de estudo', 'casos-de-estudo', ['hero', 'gallery', 'testimonials']],
                ['Contactos', 'contactos', ['hero', 'contact_form']],
            ]),
            'real-estate' => self::template('Imobiliário', 'Estrutura para imobiliárias, agentes e propriedades.', [
                ['Home', 'home', ['hero', 'image', 'feature_grid', 'cta']],
                ['Imóveis', 'imoveis', ['hero', 'product_grid', 'cta']],
                ['Sobre', 'sobre', ['hero', 'text']],
                ['Contactos', 'contactos', ['hero', 'contact_form', 'map']],
            ]),
            'landing' => self::template('Landing page', 'Página única focada numa oferta, produto ou campanha.', [
                ['Home', 'home', ['hero', 'feature_grid', 'testimonials', 'faq', 'cta']],
            ]),
            'saas' => self::template('SaaS', 'Estrutura para produtos digitais e software.', [
                ['Home', 'home', ['hero', 'feature_grid', 'pricing', 'testimonials', 'cta']],
                ['Funcionalidades', 'funcionalidades', ['hero', 'feature_grid', 'faq']],
                ['Preços', 'precos', ['hero', 'pricing', 'faq']],
                ['Contactos', 'contactos', ['hero', 'contact_form']],
            ]),
            'shop' => self::template('Loja online', 'Estrutura comercial para catálogo e vendas.', [
                ['Home', 'home', ['hero', 'feature_grid', 'product_grid', 'cta']],
                ['Loja', 'loja', ['hero', 'product_grid']],
                ['Sobre', 'sobre', ['hero', 'text']],
                ['Contactos', 'contactos', ['hero', 'contact_form']],
            ]),
            'professional' => self::template('Serviços profissionais', 'Website para consultores, advogados, contabilistas e outros serviços especializados.', [
                ['Home', 'home', ['hero', 'feature_grid', 'testimonials', 'cta']],
                ['Serviços', 'servicos', ['hero', 'feature_grid', 'faq']],
                ['Sobre nós', 'sobre-nos', ['hero', 'text']],
                ['Contactos', 'contactos', ['hero', 'contact_form', 'map']],
            ]),
        ];
    }

    /** @return array{name:string,description:string,pages:list<array{name:string,slug:string,sections:list<string>}>} */
    private static function template(string $name, string $description, array $pages): array
    {
        return compact('name', 'description', 'pages');
    }
}
