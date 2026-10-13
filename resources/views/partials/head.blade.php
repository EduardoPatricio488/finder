<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

@php
    $browserPageTitles = [
        'home' => 'Início',
        'saas.upgrade' => 'Planos',
        'products' => 'Produtos',
        'products.show' => 'Produto',
        'sales' => 'Vendas',
        'orders' => 'Encomendas',
        'users' => 'Utilizadores',
        'dashboard' => 'Meus Websites',
        'help' => 'Ajuda',
        'notifications' => 'Notificações',
        'site.create' => 'Criar website',
        'builder.edit' => 'Editor de websites',
        'account' => 'A minha conta',
        'account.favorites' => 'Favoritos',
        'orders.tracking' => 'Acompanhamento de encomendas',
        'site.public' => 'Website',
        'admin.dashboard' => 'Administração',
        'admin.websites' => 'Websites',
        'admin.config' => 'Configurações',
        'site.manage.dashboard' => 'Dashboard',
        'site.manage.config' => 'Configuração da loja',
        'site.manage.settings' => 'Configuração do site',
        'site.manage.definitions' => 'Definições',
        'site.manage.media' => 'Media',
        'site.manage.media.file' => 'Ficheiro de media',
        'site.manage.upgrade' => 'Plano do website',
        'site.manage.menus' => 'Menus',
        'site.manage.categories' => 'Categorias',
        'site.manage.payments' => 'Pagamentos',
        'site.manage.promotions' => 'Promoções',
        'site.manage.stock' => 'Movimentos de stock',
        'site.manage.submissions' => 'Submissões',
        'site.manage.switch' => 'Mudar website',
    ];
    $currentRouteName = request()->route()?->getName();
    $browserPageTitle = filled($title ?? null)
        ? $title
        : ($browserPageTitles[$currentRouteName] ?? str($currentRouteName ?? 'Finder')->afterLast('.')->replace(['-', '_'], ' ')->title()->toString());
@endphp

<title>{{ $browserPageTitle }} - {{ config('app.name', 'Finder') }}</title>

<link rel="icon" href="/site-icon.svg" type="image/svg+xml">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
