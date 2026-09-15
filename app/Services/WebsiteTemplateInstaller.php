<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Site;
use App\Models\SitePage;
use App\Models\SiteSection;
use App\Support\WebsiteBuilder\SiteSectionDocument;
use App\Support\WebsiteBuilder\WebsiteTemplates;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class WebsiteTemplateInstaller
{
    public function install(Site $site, string $templateKey): SitePage
    {
        $definition = WebsiteTemplates::get($templateKey);

        return DB::transaction(function () use ($site, $definition): SitePage {
            $site->pages()->withTrashed()->get()->each(function (SitePage $page): void {
                $page->sections()->delete();
                $page->forceDelete();
            });

            $home = null;

            foreach ($definition['pages'] as $index => $definitionPage) {
                $page = $site->pages()->create([
                    'name' => Str::limit($definitionPage['name'], 120, ''),
                    'slug' => Str::slug($definitionPage['slug']),
                    'status' => 'draft',
                    'is_homepage' => $definitionPage['slug'] === 'home',
                    'sort_order' => $index,
                    'seo' => [],
                ]);

                foreach ($definitionPage['sections'] as $sectionIndex => $definitionSection) {
                    $type = $definitionSection['type'];
                    $section = $page->sections()->create([
                        'type' => $type,
                        'label' => Str::limit($definitionSection['label'], 120, ''),
                        'content' => $this->defaultContent($type),
                        'settings' => [
                            'background' => 'transparent',
                            'padding' => 'lg',
                            'align' => 'left',
                        ],
                        'sort_order' => $sectionIndex,
                        'is_visible' => true,
                    ]);

                    $this->upgradeSectionToDocument($section);
                }

                if ($definitionPage['slug'] === 'home') {
                    $home = $page;
                }
            }

            if (! $home instanceof SitePage) {
                throw new InvalidArgumentException('O template tem de definir uma página Home.');
            }

            $site->update([
                'theme' => $site->theme ?: $this->defaultTheme(),
            ]);

            return $home;
        });
    }

    private function upgradeSectionToDocument(SiteSection $section): void
    {
        $document = SiteSectionDocument::fromSection($section);
        $data = SiteSectionDocument::toSectionData($document);
        $payload = $data[0] ?? null;

        if (! is_array($payload)) {
            throw new InvalidArgumentException('Não foi possível preparar a secção do template.');
        }

        $section->update([
            'content' => is_array($payload['content'] ?? null) ? $payload['content'] : [],
            'settings' => is_array($payload['settings'] ?? null) ? $payload['settings'] : [],
        ]);
    }

    private function defaultTheme(): array
    {
        return [
            'primary' => '#635bff',
            'secondary' => '#111827',
            'background' => '#ffffff',
            'text' => '#111827',
            'radius' => '1rem',
            'font_heading' => 'Inter',
            'font_body' => 'Inter',
        ];
    }

    private function defaultContent(string $type): array
    {
        return match ($type) {
            'hero' => [
                'title' => 'A tua marca, apresentada de forma profissional.',
                'subtitle' => 'Explica em poucas palavras o que fazes, para quem e porque é que os visitantes devem escolher-te.',
                'button_label' => 'Falar connosco',
                'button_url' => '#contacto',
            ],
            'text' => [
                'title' => 'Uma história que merece ser contada',
                'body' => 'Apresenta aqui a tua empresa, experiência, valores ou proposta de valor. Mantém o texto simples e centrado no que é importante para o visitante.',
            ],
            'image' => [
                'url' => '',
                'alt' => '',
                'caption' => 'Adiciona uma imagem relevante para esta secção.',
            ],
            'button' => [
                'label' => 'Saber mais',
                'url' => '#',
            ],
            'feature_grid' => [
                'title' => 'Porque escolher-nos',
                'items' => [
                    ['title' => 'Experiência', 'description' => 'Mostra aquilo que sabes fazer e o valor que entregas.'],
                    ['title' => 'Qualidade', 'description' => 'Explica o que torna a tua oferta especial.'],
                    ['title' => 'Confiança', 'description' => 'Dá aos visitantes uma razão clara para avançar.'],
                ],
            ],
            'card' => [
                'title' => 'Uma oferta clara',
                'description' => 'Resume aqui um serviço, produto ou vantagem importante.',
                'button_label' => 'Saber mais',
                'button_url' => '#',
            ],
            'testimonials' => [
                'title' => 'O que dizem os clientes',
                'items' => [
                    ['name' => 'Cliente', 'quote' => 'Adiciona aqui um testemunho real de um cliente.'],
                    ['name' => 'Cliente', 'quote' => 'Usa esta área para reforçar confiança e credibilidade.'],
                ],
            ],
            'faq' => [
                'title' => 'Perguntas frequentes',
                'items' => [
                    ['question' => 'Como funciona?', 'answer' => 'Explica de forma simples como funciona o teu serviço ou processo.'],
                    ['question' => 'Como posso entrar em contacto?', 'answer' => 'Indica aqui o melhor canal para falar contigo.'],
                    ['question' => 'Onde posso saber mais?', 'answer' => 'Adiciona uma ligação para a página ou recurso relevante.'],
                ],
            ],
            'gallery' => [
                'title' => 'Galeria',
                'items' => [],
            ],
            'contact_form' => [
                'title' => 'Vamos falar?',
                'description' => 'Envia uma mensagem e entraremos em contacto contigo.',
                'button_label' => 'Enviar mensagem',
            ],
            'product_grid' => [
                'title' => 'Produtos em destaque',
                'description' => 'Apresenta aqui os produtos mais relevantes.',
                'limit' => 6,
            ],
            'product_card' => [
                'title' => 'Produto',
                'description' => 'Descrição do produto.',
                'price' => '',
            ],
            'pricing' => [
                'title' => 'Escolhe a opção certa para ti',
                'items' => [
                    ['name' => 'Essencial', 'price' => 'Consultar', 'description' => 'Para começar.'],
                    ['name' => 'Profissional', 'price' => 'Consultar', 'description' => 'Para necessidades mais completas.'],
                    ['name' => 'Personalizado', 'price' => 'Sob consulta', 'description' => 'Uma solução adaptada.'],
                ],
            ],
            'blog_posts' => [
                'title' => 'Últimos artigos',
                'description' => 'Partilha conhecimento, novidades e histórias relevantes.',
            ],
            'social_links' => [
                'title' => 'Segue-nos',
                'items' => [],
            ],
            'video' => [
                'title' => 'Conhece melhor o nosso trabalho',
                'url' => '',
            ],
            'map' => [
                'title' => 'Encontra-nos',
                'address' => 'Adiciona aqui a morada.',
            ],
            'newsletter' => [
                'title' => 'Recebe as novidades',
                'description' => 'Deixa o teu email para receber novidades.',
                'button_label' => 'Subscrever',
            ],
            'cta' => [
                'title' => 'Pronto para dar o próximo passo?',
                'description' => 'Cria uma chamada à acção clara para orientar o visitante.',
                'button_label' => 'Entrar em contacto',
                'button_url' => '#contacto',
            ],
            default => [
                'title' => Str::headline($type),
                'description' => 'Personaliza esta secção com informação relevante para os teus visitantes.',
            ],
        };
    }
}
