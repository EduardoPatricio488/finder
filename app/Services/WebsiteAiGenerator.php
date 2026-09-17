<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WebsiteAiGenerator
{
    /**
     * Generate a structured website blueprint. If no AI provider is configured,
     * the service deliberately falls back to a deterministic premium blueprint.
     */
    public function generate(array $brief): array
    {
        $apiKey = trim((string) config('services.openai.key', env('OPENAI_API_KEY', '')));

        if ($apiKey === '') {
            return ['pages' => $this->fallback($brief)];
        }

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => env('OPENAI_MODEL', 'gpt-5-mini'),
                    'temperature' => 0.45,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => <<<'PROMPT'
És o director criativo e copywriter sénior do Website Builder Finder. Cria websites pessoais premium, modernos e prontos a publicar. Responde apenas com JSON válido.

O resultado é um portfolio pessoal, não um website empresarial genérico. Usa Português de Portugal. A estrutura deve ser elegante, editorial, minimalista e com excelente hierarquia visual. O conteúdo deve parecer escrito por um profissional, não por um gerador de templates.

Usa apenas estas secções: hero, text, image, button, feature_grid, card, testimonials, faq, gallery, contact_form, product_grid, product_card, pricing, blog_posts, social_links, video, map, newsletter e cta.

O JSON deve ter exactamente a forma {"pages":{"home":[{"type":"hero","label":"...","content":{}}]}}.

Para um portfolio pessoal, cria exactamente estas três páginas quando elas forem pedidas:
- home: hero forte + pequena apresentação/posicionamento + destaques baseados exclusivamente no briefing + CTA final;
- about: apresentação pessoal mais desenvolvida, mas sem inventar factos;
- contact: introdução curta + formulário de contacto + CTA.

Na homepage, o hero deve ter um título curto e forte, um subtítulo claro e uma chamada para conhecer a pessoa/contactá-la. Usa feature_grid ou card apenas quando o briefing tiver informação suficiente para sustentar os itens. Se o briefing só tiver uma descrição curta, prefere poucas secções fortes em vez de preencher a página com conteúdo artificial.

REGRA ABSOLUTA DE FACTOS: nunca inventes profissão, cargo, empresa, clientes, projetos, experiência, anos de experiência, formação, prémios, competências, localização, email, telefone, redes sociais, resultados, números ou testemunhos. Se uma informação não estiver no briefing, não a afirmes. Não uses estatísticas fictícias. Não cries testemunhos fictícios. Não cries nomes de serviços que a pessoa nunca indicou.

Podes transformar a descrição fornecida em copy mais elegante, mas sem acrescentar factos. Quando faltar informação, usa linguagem neutra e aspiracional sem apresentar isso como um facto. Não uses lorem ipsum, texto de enchimento ou frases como “adiciona aqui”.

Não incluas HTML, CSS, JavaScript ou URLs inventados. Para links internos usa apenas #sobre, #contacto ou URLs que existam explicitamente no briefing. Para o formulário de contacto não inventes dados de contacto.

Mantém cada página curta: normalmente 2 a 4 secções. O objetivo é criar um portfolio premium, respirado e convincente, não uma página cheia de blocos.
PROMPT,
                        ],
                        [
                            'role' => 'user',
                            'content' => json_encode($brief, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        ],
                    ],
                ]);

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
        $requestedPages = array_values(array_unique($brief['pages'] ?? ['home', 'about', 'contact']));

        foreach ($requestedPages as $slug) {
            $sections = $pages[$slug] ?? [];
            if (! is_array($sections)) {
                $sections = [];
            }

            $result[$slug] = [];
            foreach (array_slice($sections, 0, 8) as $section) {
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
        $name = trim((string) ($brief['business_name'] ?? 'O teu nome')) ?: 'O teu nome';
        $description = trim((string) ($brief['description'] ?? ''));
        $description = $description !== '' ? $description : 'Uma presença digital pessoal, simples e profissional.';
        $pages = array_values(array_unique($brief['pages'] ?? ['home', 'about', 'contact']));
        $result = [];

        foreach ($pages as $slug) {
            $result[$slug] = match ($slug) {
                'home' => [
                    [
                        'type' => 'hero',
                        'label' => 'Apresentação',
                        'content' => [
                            'title' => $name,
                            'subtitle' => $description,
                            'button_label' => 'Conhecer melhor',
                            'button_url' => '#sobre',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => 'Introdução',
                        'content' => [
                            'title' => 'Um pouco sobre mim',
                            'body' => $description,
                        ],
                    ],
                    [
                        'type' => 'cta',
                        'label' => 'Contacto',
                        'content' => [
                            'title' => 'Vamos conversar?',
                            'description' => 'Se quiseres saber mais, entra em contacto.',
                            'button_label' => 'Contactar',
                            'button_url' => '#contacto',
                        ],
                    ],
                ],
                'about' => [
                    [
                        'type' => 'hero',
                        'label' => 'Sobre mim',
                        'content' => [
                            'title' => 'Sobre mim',
                            'subtitle' => $description,
                            'button_label' => 'Entrar em contacto',
                            'button_url' => '#contacto',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => 'Perfil',
                        'content' => [
                            'title' => 'A minha história',
                            'body' => $description,
                        ],
                    ],
                ],
                'contact' => [
                    [
                        'type' => 'hero',
                        'label' => 'Contacto',
                        'content' => [
                            'title' => 'Vamos falar?',
                            'subtitle' => 'Se tens uma questão ou queres saber mais, estou disponível para conversar.',
                            'button_label' => 'Enviar mensagem',
                            'button_url' => '#formulario',
                        ],
                    ],
                    [
                        'type' => 'contact_form',
                        'label' => 'Formulário',
                        'content' => [
                            'title' => 'Entra em contacto',
                            'description' => 'Envia uma mensagem através do formulário.',
                            'button_label' => 'Enviar mensagem',
                        ],
                    ],
                ],
                default => [
                    [
                        'type' => 'text',
                        'label' => ucfirst($slug),
                        'content' => [
                            'title' => ucfirst($slug),
                            'body' => $description,
                        ],
                    ],
                ],
            };
        }

        return $result;
    }
}
