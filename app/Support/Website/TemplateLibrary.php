<?php

declare(strict_types=1);

namespace App\Support\Website;

final class TemplateLibrary
{
    /**
     * Devolve a estrutura completa (todas as páginas possíveis) para um template,
     * já com conteúdo real e coerente — não apenas placeholders.
     *
     * @return array<string, list<array{type:string,label?:string,content:array}>>
     */
    public static function pages(string $template, array $context): array
    {
        $context = self::withDefaults($context);

        return match ($template) {
            'commerce' => self::commerce($context),
            'elegant' => self::elegant($context),
            'creative' => self::creative($context),
            'professional' => self::professional($context),
            'launch' => self::launch($context),
            default => self::studio($context),
        };
    }

    private static function withDefaults(array $context): array
    {
        return [
            'name' => trim((string) ($context['name'] ?? '')) ?: 'O teu negócio',
            'description' => trim((string) ($context['description'] ?? '')),
            'audience' => trim((string) ($context['audience'] ?? '')),
            'goal_label' => (string) ($context['goal_label'] ?? 'Saber mais'),
            'style' => (string) ($context['style'] ?? 'Profissional'),
            'headline' => trim((string) ($context['headline'] ?? '')),
            'about' => trim((string) ($context['about'] ?? '')),
            'featured_products' => trim((string) ($context['featured_products'] ?? '')),
            'shipping' => trim((string) ($context['shipping'] ?? '')),
            'returns' => trim((string) ($context['returns'] ?? '')),
            'contact_email' => trim((string) ($context['contact_email'] ?? '')),
        ];
    }

    // ================= STUDIO (negócio genérico / serviços) =================

    private static function studio(array $c): array
    {
        $name = $c['name'];
        $desc = $c['description'] ?: "Ajudamos {$c['audience_or_generic']} a resolver problemas reais, com um serviço próximo e resultados que se sentem desde o primeiro dia.";

        return [
            'home' => [
                self::hero($name, $desc ?: "$name existe para simplificar o teu dia a dia, com um serviço próximo e resultados que se sentem.", $c['goal_label']),
                self::features('Porque trabalhar connosco', [
                    ['title' => 'Acompanhamento próximo', 'description' => 'Falas sempre com quem trata do teu projecto — sem intermediários nem respostas genéricas.'],
                    ['title' => 'Experiência comprovada', 'description' => 'Anos a resolver problemas parecidos com o teu, com processos já testados.'],
                    ['title' => 'Resultados claros', 'description' => 'Sabes sempre em que ponto está o teu projecto e o que vem a seguir.'],
                ]),
                self::testimonials([
                    ['name' => 'Cliente satisfeito', 'quote' => 'Desde o primeiro contacto senti que estava em boas mãos. Recomendo sem hesitar.'],
                    ['name' => 'Parceiro de longa data', 'quote' => 'Profissionalismo e cuidado em cada detalhe. Voltaríamos a trabalhar juntos sem pensar duas vezes.'],
                ]),
                self::cta('Vamos conversar sobre o teu projecto?', 'Conta-nos o que precisas — respondemos rapidamente com os próximos passos.', $c['goal_label']),
            ],
            'about' => [
                self::hero('Sobre nós', "$name nasceu da vontade de fazer as coisas bem feitas, sem atalhos e sem promessas vazias.", 'Falar connosco'),
                self::text('A nossa história', "Começámos por resolver um problema simples: encontrar quem faça um bom trabalho, a sério e a tempo. Desde então, temos vindo a construir uma equipa e um processo pensados para dar aos nossos clientes exactamente isso — sem complicações."),
                self::features('Os nossos valores', [
                    ['title' => 'Transparência', 'description' => 'Sabes sempre o que estás a pagar e porquê.'],
                    ['title' => 'Compromisso', 'description' => 'Cumprimos o que combinamos, nos prazos que combinamos.'],
                    ['title' => 'Melhoria contínua', 'description' => 'Cada projecto ensina-nos algo que aplicamos ao seguinte.'],
                ]),
            ],
            'services' => [
                self::hero('Serviços', 'Soluções pensadas para o teu negócio, do primeiro contacto à entrega final.', 'Pedir uma proposta'),
                self::features('O que fazemos', [
                    ['title' => 'Consultoria inicial', 'description' => 'Analisamos o teu caso e propomos o caminho mais eficaz.'],
                    ['title' => 'Implementação', 'description' => 'Tratamos de tudo com actualizações regulares sobre o progresso.'],
                    ['title' => 'Acompanhamento pós-entrega', 'description' => 'Continuamos disponíveis depois do projecto estar concluído.'],
                ]),
                self::cta('Ainda tens dúvidas?', 'Fala com a nossa equipa e esclarece tudo antes de avançar.', 'Contactar'),
            ],
            'products' => [
                self::hero('Os nossos produtos', 'Uma selecção pensada para dar resposta às tuas necessidades.', 'Ver catálogo'),
                self::productGrid('Produtos em destaque', 'Os artigos mais escolhidos pelos nossos clientes.'),
            ],
            'gallery' => [
                self::hero('Galeria', 'Alguns momentos e trabalhos que mostram o que fazemos.', 'Contactar'),
                self::gallery(),
            ],
            'pricing' => self::pricingPage('Planos pensados para diferentes fases do teu negócio.'),
            'faq' => self::faqPage([
                ['question' => 'Quanto tempo demora um projecto típico?', 'answer' => 'Depende do âmbito, mas na primeira conversa já conseguimos dar-te uma estimativa realista.'],
                ['question' => 'Trabalham com orçamento fechado?', 'answer' => 'Sim — depois de perceber o que precisas, enviamos uma proposta com valor e prazo fechados.'],
                ['question' => 'E se eu não souber bem o que preciso?', 'answer' => 'Sem problema. Ajudamos-te a clarificar o problema antes de avançar para a solução.'],
            ]),
            'contact' => self::contactPage("Tens uma pergunta ou um projecto em mente? Escreve-nos — respondemos o mais rápido possível."),
            'blog' => self::blogPage('Artigos e novidades', 'Partilhamos aqui reflexões, casos de sucesso e novidades da equipa.'),
            'default' => [
                self::hero($name, $c['description'] ?: 'Apresenta aqui o essencial do teu negócio.', $c['goal_label']),
                self::text('Sobre esta página', 'Personaliza este espaço com a informação mais relevante para quem visita o teu website.'),
                self::cta('Fala connosco', 'Estamos disponíveis para ajudar.', 'Contactar'),
            ],
        ];
    }

    // ================= COMMERCE (loja online) =================

    private static function commerce(array $c): array
    {
        $name = $c['name'];
        $description = $c['description'] ?: 'Uma loja online criada para apresentar os produtos de forma simples, clara e elegante.';
        $headline = $c['headline'] ?: $description;
        $about = $c['about'] ?: '';
        $featured = $c['featured_products'] ?: '';
        $shipping = $c['shipping'] ?: '';
        $returns = $c['returns'] ?: '';
        $contactEmail = $c['contact_email'] ?: '';

        $brandText = $about !== ''
            ? $about
            : 'Conhece a marca, explora a coleção e escolhe os produtos que fazem sentido para ti.';

        $productText = $featured !== ''
            ? $featured
            : 'Os produtos disponíveis aparecem aqui automaticamente a partir do catálogo da loja.';

        $shippingText = $shipping !== ''
            ? $shipping
            : 'As condições de envio são apresentadas de acordo com a configuração real da loja.';

        $returnsText = $returns !== ''
            ? $returns
            : 'Consulta as condições de trocas e devoluções definidas pela loja antes de finalizar a compra.';

        return [
            'home' => [
                self::hero($name, $headline, 'Ver produtos'),
                self::text('A coleção', $productText),
                self::productGrid('Produtos em destaque', 'Explora o catálogo disponível na loja.'),
                self::text('A marca', $brandText),
                self::cta('Encontra o que procuras', 'Explora os produtos disponíveis e conhece melhor a nossa marca.', 'Ver produtos'),
            ],
            'products' => [
                self::hero('Produtos', 'Explora o catálogo da loja e encontra o que procuras.', 'Ver produtos'),
                self::productGrid('Catálogo', 'Os produtos reais da loja são apresentados automaticamente nesta página.'),
            ],
            'about' => [
                self::hero('Sobre a marca', $brandText, 'Ver produtos'),
                self::text('A nossa história', $about !== '' ? $about : $description),
                self::productGrid('Da nossa coleção', 'Conhece alguns dos produtos disponíveis na loja.'),
                self::cta('Conhece a coleção', 'Quando estiveres pronto, explora os produtos disponíveis.', 'Ver produtos'),
            ],
            'contact' => [
                self::hero('Contactos', 'Precisas de ajuda com um produto ou uma encomenda? Entra em contacto.', 'Contactar'),
                [
                    'type' => 'contact_form',
                    'label' => 'Contacto',
                    'content' => [
                        'title' => 'Fala connosco',
                        'description' => $contactEmail !== '' ? "Envia a tua mensagem ou contacta-nos através de {$contactEmail}." : 'Envia a tua mensagem através do formulário.',
                        'button_label' => 'Enviar mensagem',
                    ],
                ],
                self::text('Envios e entregas', $shippingText),
                self::text('Trocas e devoluções', $returnsText),
            ],
            'faq' => self::faqPage([
                ['question' => 'Como posso saber mais sobre um produto?', 'answer' => 'Consulta a página do produto ou entra em contacto connosco para esclarecer dúvidas.'],
                ['question' => 'Onde encontro as condições de envio?', 'answer' => $shippingText],
                ['question' => 'Onde encontro as condições de troca e devolução?', 'answer' => $returnsText],
            ]),
            'gallery' => [
                self::hero('Galeria', 'Uma seleção visual da marca e dos produtos.', 'Ver produtos'),
                self::gallery(),
                self::productGrid('Produtos', 'Explora os produtos reais disponíveis na loja.'),
            ],
            'blog' => self::blogPage('Novidades da loja', 'Conteúdo e novidades da marca podem ser publicados aqui.'),
            'services' => [],
            'pricing' => [],
            'default' => [
                self::hero($name, $description, 'Ver produtos'),
                self::productGrid('Produtos', 'Explora o catálogo da loja.'),
            ],
        ];
    }

    // ================= ELEGANT (restaurante / hotel / beleza) =================

    private static function elegant(array $c): array
    {
        $name = $c['name'];

        return [
            'home' => [
                self::hero($name, $c['description'] ?: 'Uma experiência pensada ao pormenor, do primeiro momento até ao último.', 'Reservar'),
                self::gallery(),
                self::features('O que nos distingue', [
                    ['title' => 'Ambiente cuidado', 'description' => 'Cada detalhe foi pensado para proporcionar uma experiência memorável.'],
                    ['title' => 'Atendimento dedicado', 'description' => 'A nossa equipa está aqui para que te sintas bem-vindo do início ao fim.'],
                    ['title' => 'Qualidade consistente', 'description' => 'Mantemos o mesmo padrão de excelência em cada visita.'],
                ]),
                self::testimonials([
                    ['name' => 'Cliente habitual', 'quote' => 'Sempre que aqui venho sei que vou sair satisfeito. Atendimento impecável.'],
                    ['name' => 'Visita especial', 'quote' => 'Escolhemos este espaço para uma ocasião especial e superou todas as expectativas.'],
                ]),
                self::cta('Reserva já o teu momento', 'Os lugares são limitados — garante já o teu.', 'Reservar agora'),
            ],
            'about' => [
                self::hero('A nossa história', "$name nasceu da paixão por criar experiências que ficam na memória.", 'Reservar'),
                self::text('Como tudo começou', 'O que começou como um pequeno projecto tornou-se num espaço de referência, mantendo sempre o mesmo cuidado com cada detalhe e cada visitante.'),
            ],
            'services' => [
                self::hero('A experiência', 'Conhece tudo o que temos preparado para ti.', 'Reservar'),
                self::features('Momentos que oferecemos', [
                    ['title' => 'Experiência clássica', 'description' => 'Tudo o que precisas para um momento perfeito.'],
                    ['title' => 'Experiência premium', 'description' => 'Um serviço ainda mais cuidado, para ocasiões especiais.'],
                    ['title' => 'Eventos privados', 'description' => 'O espaço todo pensado à tua medida.'],
                ]),
            ],
            'products' => [
                self::hero('Menu', 'Cada opção foi pensada com cuidado, do início ao fim.', 'Reservar'),
                self::productGrid('Destaques do menu', 'As escolhas preferidas de quem nos visita.'),
            ],
            'gallery' => [
                self::hero('Galeria', 'Um vislumbre da experiência que te espera.', 'Reservar'),
                self::gallery(),
            ],
            'pricing' => self::pricingPage('Conhece as diferentes formas de viver esta experiência.'),
            'faq' => self::faqPage([
                ['question' => 'É preciso reservar com antecedência?', 'answer' => 'Recomendamos reservar com pelo menos 48 horas de antecedência, especialmente ao fim de semana.'],
                ['question' => 'Fazem eventos privados?', 'answer' => 'Sim, organizamos eventos privados mediante marcação prévia.'],
                ['question' => 'Qual o horário de funcionamento?', 'answer' => 'Consulta os nossos horários actualizados na página de contactos.'],
            ]),
            'contact' => self::contactPage('Reserva o teu lugar ou esclarece qualquer dúvida connosco.'),
            'blog' => self::blogPage('Novidades', 'Eventos, novidades do menu e histórias por trás da nossa experiência.'),
            'default' => self::studio($c)['default'],
        ];
    }

    // ================= CREATIVE (portfólio / fotografia / agência) =================

    private static function creative(array $c): array
    {
        $name = $c['name'];

        return [
            'home' => [
                self::hero($name, $c['description'] ?: 'Trabalho criativo, feito com propósito e atenção ao detalhe.', 'Ver portfólio'),
                self::gallery(),
                self::features('Como trabalhamos', [
                    ['title' => 'Escuta atenta', 'description' => 'Começamos sempre por perceber a tua visão e objectivos.'],
                    ['title' => 'Execução cuidada', 'description' => 'Cada projecto recebe a atenção que merece, do conceito à entrega.'],
                    ['title' => 'Entrega no prazo', 'description' => 'Cumprimos prazos sem comprometer a qualidade.'],
                ]),
                self::testimonials([
                    ['name' => 'Cliente satisfeito', 'quote' => 'Entenderam exactamente o que precisávamos e entregaram acima das expectativas.'],
                ]),
                self::cta('Tens um projecto em mente?', 'Conta-nos a tua ideia e vamos torná-la realidade.', 'Falar sobre o projecto'),
            ],
            'about' => [
                self::hero('Sobre', "$name é o resultado de anos a aperfeiçoar um olhar próprio sobre criatividade.", 'Ver portfólio'),
                self::text('O nosso percurso', 'Cada projecto que fazemos acrescenta algo novo ao nosso processo. É essa curiosidade constante que nos move.'),
            ],
            'services' => [
                self::hero('Serviços', 'Do conceito à execução final.', 'Pedir orçamento'),
                self::features('O que oferecemos', [
                    ['title' => 'Direcção criativa', 'description' => 'Definimos o conceito e a linguagem visual do teu projecto.'],
                    ['title' => 'Produção', 'description' => 'Tratamos de toda a execução técnica com qualidade profissional.'],
                    ['title' => 'Entrega e revisões', 'description' => 'Ajustamos o resultado final às tuas necessidades.'],
                ]),
            ],
            'products' => [
                self::hero('Trabalhos', 'Uma selecção de projectos recentes.', 'Ver portfólio'),
                self::productGrid('Projectos em destaque', 'Alguns dos trabalhos de que mais nos orgulhamos.'),
            ],
            'gallery' => [
                self::hero('Portfólio', 'Uma amostra do nosso trabalho mais recente.', 'Falar connosco'),
                self::gallery(),
            ],
            'pricing' => self::pricingPage('Escolhe o pacote mais adequado ao teu projecto.'),
            'faq' => self::faqPage([
                ['question' => 'Quanto tempo demora um projecto?', 'answer' => 'Varia consoante o âmbito — na proposta inicial já indicamos um prazo estimado.'],
                ['question' => 'Fazem revisões depois da entrega?', 'answer' => 'Sim, incluímos uma ronda de ajustes em todos os projectos.'],
                ['question' => 'Trabalham com clientes fora de Portugal?', 'answer' => 'Sim, trabalhamos remotamente com clientes de qualquer lugar.'],
            ]),
            'contact' => self::contactPage('Conta-nos a tua ideia — respondemos com uma proposta.'),
            'blog' => self::blogPage('Diário', 'Processos criativos, bastidores e novidades da equipa.'),
            'default' => self::studio($c)['default'],
        ];
    }

    // ================= PROFESSIONAL (advogado / clínica / imobiliário) =================

    private static function professional(array $c): array
    {
        $name = $c['name'];

        return [
            'home' => [
                self::hero($name, $c['description'] ?: 'Apoio especializado, com rigor e total confidencialidade.', 'Marcar consulta'),
                self::features('Porque escolher-nos', [
                    ['title' => 'Experiência sólida', 'description' => 'Anos de prática dedicados a resolver casos como o teu.'],
                    ['title' => 'Rigor e confidencialidade', 'description' => 'Tratamos cada caso com a discrição e o cuidado que merece.'],
                    ['title' => 'Acompanhamento próximo', 'description' => 'Manténs-te sempre informado sobre o progresso do teu processo.'],
                ]),
                self::testimonials([
                    ['name' => 'Cliente acompanhado', 'quote' => 'Senti-me sempre acompanhado e bem informado durante todo o processo.'],
                ]),
                self::cta('Precisas de ajuda especializada?', 'Marca uma primeira consulta e esclarece a tua situação.', 'Marcar consulta'),
            ],
            'about' => [
                self::hero('Sobre nós', "$name dedica-se a apoiar quem precisa de uma resposta séria e experiente.", 'Marcar consulta'),
                self::text('A nossa abordagem', 'Acreditamos que cada caso é único e merece atenção individual — por isso começamos sempre por ouvir antes de agir.'),
            ],
            'services' => [
                self::hero('Áreas de actuação', 'Conhece as áreas em que podemos apoiar-te.', 'Marcar consulta'),
                self::features('Como podemos ajudar', [
                    ['title' => 'Consulta inicial', 'description' => 'Analisamos a tua situação e explicamos as opções disponíveis.'],
                    ['title' => 'Acompanhamento contínuo', 'description' => 'Tratamos do teu processo do início ao fim, com actualizações regulares.'],
                    ['title' => 'Aconselhamento especializado', 'description' => 'Apoio dedicado em cada etapa do processo.'],
                ]),
            ],
            'products' => [
                self::hero('Os nossos imóveis', 'Uma selecção actualizada de oportunidades.', 'Ver imóveis'),
                self::productGrid('Imóveis em destaque', 'Oportunidades seleccionadas para ti.'),
            ],
            'gallery' => [
                self::hero('Galeria', 'Conhece melhor o nosso espaço e a nossa equipa.', 'Marcar consulta'),
                self::gallery(),
            ],
            'pricing' => self::pricingPage('Conhece as diferentes formas de trabalharmos juntos.'),
            'faq' => self::faqPage([
                ['question' => 'Como marco uma primeira consulta?', 'answer' => 'Preenche o formulário de contacto ou liga-nos directamente — respondemos o mais rápido possível.'],
                ['question' => 'A primeira consulta tem custo?', 'answer' => 'A primeira consulta serve para perceber a tua situação; os detalhes de custo são sempre explicados com antecedência.'],
                ['question' => 'O meu caso é tratado com confidencialidade?', 'answer' => 'Sim, a confidencialidade é uma prioridade em todos os casos que acompanhamos.'],
            ]),
            'contact' => self::contactPage('Marca uma consulta ou esclarece as tuas dúvidas connosco.'),
            'blog' => self::blogPage('Artigos', 'Informação útil e actualizada sobre temas relevantes para ti.'),
            'default' => self::studio($c)['default'],
        ];
    }

    // ================= LAUNCH (startup / landing page) =================

    private static function launch(array $c): array
    {
        $name = $c['name'];

        return [
            'home' => [
                self::hero($name, $c['description'] ?: 'A ferramenta que faltava para simplificares o teu trabalho.', $c['goal_label']),
                self::features('Porque vais adorar', [
                    ['title' => 'Simples de usar', 'description' => 'Sem curva de aprendizagem — começas a usar em minutos.'],
                    ['title' => 'Resultados rápidos', 'description' => 'Vês o impacto desde os primeiros dias de utilização.'],
                    ['title' => 'Suporte dedicado', 'description' => 'A nossa equipa está sempre disponível para te ajudar.'],
                ]),
                self::pricingSection('Escolhe o teu plano', 'Sem surpresas, cancela quando quiseres.'),
                self::testimonials([
                    ['name' => 'Utilizador desde o início', 'quote' => 'Mudou completamente a forma como organizamos o nosso trabalho diário.'],
                ]),
                self::cta('Pronto para começar?', 'Experimenta hoje mesmo, sem compromisso.', 'Começar agora'),
            ],
            'about' => [
                self::hero('A nossa missão', "$name nasceu para resolver um problema real de forma simples.", 'Começar agora'),
                self::text('Porque existimos', 'Vimos demasiadas pessoas a perder tempo com soluções complicadas. Decidimos construir algo diferente — simples, rápido e eficaz.'),
            ],
            'services' => [
                self::hero('Funcionalidades', 'Tudo o que precisas, numa só ferramenta.', 'Começar agora'),
                self::features('O que oferecemos', [
                    ['title' => 'Configuração rápida', 'description' => 'Começa a usar em poucos minutos, sem complicações.'],
                    ['title' => 'Integrações', 'description' => 'Liga-te às ferramentas que já usas todos os dias.'],
                    ['title' => 'Relatórios claros', 'description' => 'Acompanha os resultados de forma simples e visual.'],
                ]),
            ],
            'products' => [
                self::hero('Planos', 'Escolhe a opção que melhor se adapta a ti.', 'Ver planos'),
                self::productGrid('Planos disponíveis', 'Compara as opções e escolhe a ideal.'),
            ],
            'gallery' => [
                self::hero('Como funciona', 'Um vislumbre da experiência dentro da ferramenta.', 'Começar agora'),
                self::gallery(),
            ],
            'pricing' => self::pricingPage('Planos simples e transparentes, sem letras pequenas.'),
            'faq' => self::faqPage([
                ['question' => 'Posso cancelar quando quiser?', 'answer' => 'Sim, sem período de fidelização e sem custos escondidos.'],
                ['question' => 'Existe período de teste gratuito?', 'answer' => 'Sim, podes experimentar antes de decidires qual o plano ideal.'],
                ['question' => 'Preciso de conhecimentos técnicos?', 'answer' => 'Não — a ferramenta foi pensada para ser simples desde o primeiro minuto.'],
            ]),
            'contact' => self::contactPage('Tens alguma questão antes de começar? Fala connosco.'),
            'blog' => self::blogPage('Novidades', 'Actualizações do produto e conteúdo para tirares mais partido da ferramenta.'),
            'default' => self::studio($c)['default'],
        ];
    }

    // ================= Blocos reutilizáveis =================

    private static function hero(string $title, string $subtitle, string $buttonLabel): array
    {
        return ['type' => 'hero', 'content' => ['title' => $title, 'subtitle' => $subtitle, 'button_label' => $buttonLabel, 'button_url' => '#contacto']];
    }

    private static function text(string $title, string $body): array
    {
        return ['type' => 'text', 'content' => ['title' => $title, 'body' => $body]];
    }

    private static function features(string $title, array $items): array
    {
        return ['type' => 'feature_grid', 'content' => ['title' => $title, 'items' => $items]];
    }

    private static function testimonials(array $items): array
    {
        return ['type' => 'testimonials', 'content' => ['title' => 'O que dizem de nós', 'items' => $items]];
    }

    private static function gallery(): array
    {
        return ['type' => 'gallery', 'content' => ['title' => 'Galeria', 'items' => [
            ['url' => '', 'alt' => '', 'caption' => 'Adiciona a primeira imagem.'],
            ['url' => '', 'alt' => '', 'caption' => 'Adiciona mais uma imagem.'],
            ['url' => '', 'alt' => '', 'caption' => 'Adiciona mais uma imagem.'],
        ]]];
    }

    private static function productGrid(string $title, string $description): array
    {
        return ['type' => 'product_grid', 'content' => ['title' => $title, 'description' => $description, 'limit' => 6]];
    }

    private static function cta(string $title, string $description, string $buttonLabel): array
    {
        return ['type' => 'cta', 'content' => ['title' => $title, 'description' => $description, 'button_label' => $buttonLabel, 'button_url' => '#contacto']];
    }

    private static function pricingSection(string $title, string $description): array
    {
        return ['type' => 'pricing', 'content' => ['title' => $title, 'description' => $description, 'items' => [
            ['name' => 'Essencial', 'price' => '19 €/mês', 'description' => 'Para quem está a começar.', 'features' => ['Funcionalidade principal', 'Suporte por email'], 'button_label' => 'Escolher', 'button_url' => '#'],
            ['name' => 'Profissional', 'price' => '49 €/mês', 'description' => 'Para equipas em crescimento.', 'features' => ['Tudo do plano Essencial', 'Relatórios avançados', 'Suporte prioritário'], 'button_label' => 'Escolher', 'button_url' => '#'],
        ]]];
    }

    private static function pricingPage(string $intro): array
    {
        return [
            self::hero('Planos e preços', $intro, 'Contactar'),
            self::pricingSection('Escolhe a opção certa para ti', 'Sem compromissos de longo prazo.'),
        ];
    }

    private static function faqPage(array $items): array
    {
        return [
            self::hero('Perguntas frequentes', 'Reunimos aqui as respostas às dúvidas mais comuns.', 'Contactar'),
            ['type' => 'faq', 'content' => ['title' => 'Perguntas frequentes', 'items' => $items]],
        ];
    }

    private static function contactPage(string $intro): array
    {
        return [
            self::hero('Contacta-nos', $intro, 'Enviar mensagem'),
            ['type' => 'contact_form', 'content' => ['title' => 'Entra em contacto', 'description' => 'Preenche o formulário e entraremos em contacto o mais rápido possível.', 'button_label' => 'Enviar mensagem']],
            ['type' => 'map', 'content' => ['address' => '', 'embed_url' => '']],
        ];
    }

    private static function blogPage(string $title, string $description): array
    {
        return [
            self::hero($title, $description, 'Ver artigos'),
            ['type' => 'blog_posts', 'content' => ['title' => $title, 'items' => [
                ['title' => 'Primeiro artigo', 'excerpt' => 'Adiciona aqui um resumo apelativo do teu primeiro artigo.', 'url' => '#', 'image' => ''],
            ]]],
        ];
    }
}
