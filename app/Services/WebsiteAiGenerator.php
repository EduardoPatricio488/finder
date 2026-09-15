<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WebsiteAiGenerator
{
    /**
     * Generate a structured website blueprint. If no AI provider is configured,
     * the service deliberately falls back to the deterministic Builder blueprint.
     */
    public function generate(array $brief): array
    {
        $apiKey = trim((string) config('services.openai.key', ''));

        if ($apiKey === '') {
            return ['pages' => $this->fallback($brief)];
        }

        $systemPrompt = implode("\n", [
            'És o gerador de estrutura do Website Builder Finder. Responde apenas com JSON válido.',
            'Cria uma estrutura de website em Português de Portugal. Não inventes tipos de secção: usa apenas hero, text, image, button, feature_grid, card, testimonials, faq, gallery, contact_form, product_grid, product_card, pricing, blog_posts, social_links, video, map, newsletter e cta.',
            'O JSON deve ter exactamente a forma {"pages":{"home":[{"type":"hero","label":"...","content":{}}]}}. Mantém o conteúdo simples, profissional e editável. Não incluas HTML, CSS, JavaScript, URLs inventados, dados pessoais ou afirmações factuais que não estejam no briefing. Usa placeholders claros quando faltar informação.',
        ]);

        $payload = [
            'model' => (string) config('services.openai.model', 'gpt-5-mini'),
            'temperature' => 0.4,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt,
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($brief, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ],
            ],
        ];

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', $payload);

            if ($response->successful()) {
                $pages = data_get($response->json(), 'choices.0.message.content');
                $decoded = is_string($pages) ? json_decode($pages, true) : null;

                if (is_array($decoded) && isset($decoded['pages']) && is_array($decoded['pages'])) {
                    return ['pages' => $this->sanitize($decoded['pages'], $brief)];
                }
            }
        } catch (\Throwable) {
            // Never prevent website creation because an external AI provider failed.
        }

        return ['pages' => $this->fallback($brief)];
    }

    private function sanitize(array $pages, array $brief): array
    {
        $allowed = [
            'hero', 'text', 'image', 'button', 'feature_grid', 'card', 'testimonials',
            'faq', 'gallery', 'contact_form', 'product_grid', 'product_card', 'pricing',
            'blog_posts', 'social_links', 'video', 'map', 'newsletter', 'cta',
        ];
        $result = [];

        foreach ($pages as $slug => $sections) {
            if (! is_array($sections)) {
                continue;
            }

            $result[$slug] = [];
            foreach (array_slice($sections, 0, 20) as $section) {
                if (! is_array($section) || ! in_array($section['type'] ?? '', $allowed, true)) {
                    continue;
                }

                $result[$slug][] = [
                    'type' => $section['type'],
                    'label' => is_string($section['label'] ?? null) ? mb_substr($section['label'], 0, 80) : null,
                    'content' => is_array($section['content'] ?? null) ? $section['content'] : [],
                ];
            }
        }

        return $result ?: $this->fallback($brief);
    }

    private function fallback(array $brief): array
    {
        $name = (string) ($brief['business_name'] ?? 'O teu negócio');
        $description = (string) ($brief['description'] ?? 'Uma presença digital profissional.');
        $pages = array_values(array_unique($brief['pages'] ?? ['home', 'about', 'contact']));
        $result = [];

        foreach ($pages as $slug) {
            $result[$slug] = match ($slug) {
                'home' => [
                    ['type' => 'hero', 'content' => ['title' => $name, 'subtitle' => $description, 'button_label' => 'Saber mais', 'button_url' => '#contacto']],
                    ['type' => 'feature_grid', 'content' => ['title' => 'O que oferecemos', 'items' => [
                        ['title' => 'Qualidade', 'description' => 'Explica aqui o principal benefício.'],
                        ['title' => 'Experiência', 'description' => 'Mostra a experiência que te distingue.'],
                        ['title' => 'Confiança', 'description' => 'Ajuda os visitantes a tomar uma decisão.'],
                    ]]],
                    ['type' => 'cta', 'content' => ['title' => 'Vamos falar?', 'description' => 'Entra em contacto para saber mais.', 'button_label' => 'Contactar', 'button_url' => '#contacto']],
                ],
                'about' => [
                    ['type' => 'hero', 'content' => ['title' => 'Sobre nós', 'subtitle' => $description, 'button_label' => 'Contactar', 'button_url' => '#contacto']],
                    ['type' => 'text', 'content' => ['title' => 'A nossa história', 'body' => 'Adiciona aqui a história, os valores e a experiência do teu negócio.']],
                ],
                'services' => [
                    ['type' => 'hero', 'content' => ['title' => 'Serviços', 'subtitle' => 'Conhece as soluções que disponibilizamos.', 'button_label' => 'Saber mais', 'button_url' => '#contacto']],
                    ['type' => 'feature_grid', 'content' => ['title' => 'Os nossos serviços', 'items' => [
                        ['title' => 'Serviço principal', 'description' => 'Descrição do serviço.'],
                        ['title' => 'Serviço adicional', 'description' => 'Descrição do serviço.'],
                        ['title' => 'Acompanhamento', 'description' => 'Descrição do serviço.'],
                    ]]],
                ],
                'products' => [
                    ['type' => 'hero', 'content' => ['title' => 'Produtos', 'subtitle' => 'Conhece o nosso catálogo.', 'button_label' => 'Ver produtos', 'button_url' => '#produtos']],
                    ['type' => 'product_grid', 'content' => ['title' => 'Catálogo', 'description' => 'Os teus produtos aparecem aqui.', 'limit' => 6]],
                ],
                'contact' => [
                    ['type' => 'hero', 'content' => ['title' => 'Contacta-nos', 'subtitle' => 'Estamos disponíveis para ajudar.', 'button_label' => 'Enviar mensagem', 'button_url' => '#formulario']],
                    ['type' => 'contact_form', 'content' => ['title' => 'Entra em contacto', 'description' => 'Envia-nos uma mensagem.', 'button_label' => 'Enviar mensagem']],
                ],
                default => [
                    ['type' => 'text', 'content' => ['title' => ucfirst($slug), 'body' => 'Personaliza esta página com a informação mais importante para os teus visitantes.']],
                ],
            };
        }

        return $result;
    }
}
