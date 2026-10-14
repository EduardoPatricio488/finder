@php
    $brief = data_get($site->settings, 'builder.brief', []);
    $headline = trim((string) (collect(explode("\n", (string) ($brief['additional_info'] ?? '')))->first(fn ($line) => str_starts_with($line, 'Mensagem principal:')) ?? ''));
    $headline = $headline !== '' ? trim(str_replace('Mensagem principal:', '', $headline)) : '';
    $about = trim((string) ($brief['description'] ?? ''));
    $additionalInfo = trim((string) ($brief['additional_info'] ?? ''));
    $contactEmail = '';
    foreach (explode("\n", $additionalInfo) as $line) {
        if (str_starts_with($line, 'Email de apoio:')) {
            $contactEmail = trim(str_replace('Email de apoio:', '', $line));
        }
    }
    $accent = $site->theme['primary'] ?? $site->primary_color ?? '#059669';
    $sidebarType = (string) data_get($site->settings, 'sidebar.type', 'fixed');
    $sidebarAppearance = (string) data_get($site->settings, 'sidebar.appearance', 'light');
    $sidebarWidth = (string) data_get($site->settings, 'sidebar.width', 'normal');
    $sidebarIcons = (string) data_get($site->settings, 'sidebar.icons', 'shown');
    $sidebarPosition = (string) data_get($site->settings, 'sidebar.position', 'left');
    $sidebarPages = $site->pages->sortBy('sort_order')->values();
    if (! $preview) {
        $sidebarPages = $sidebarPages->where('status', 'published')->values();
    }
    $sidebarEnabled = $sidebarPages->isNotEmpty();
    $currentPage = $page->slug;
    $menuApplied = filled(data_get($site->settings, 'menu_applied_at'));
    $activeMenu = $menuApplied ? $site->menus->firstWhere('location', 'header') : null;
    $menuItems = $activeMenu?->items?->where('is_visible', true)->whereNull('parent_id')->values() ?? collect();
    $labels = ['home' => 'Início', 'products' => 'Produtos', 'about' => 'Sobre a marca', 'contact' => 'Contactos', 'faq' => 'FAQ', 'gallery' => 'Galeria', 'blog' => 'Blog'];
    $visiblePages = $site->pages->sortBy('sort_order')->whereIn('slug', array_keys($labels));
    $productCount = $site->products->count();
@endphp

<div class="{{ $sidebarEnabled ? (in_array($sidebarPosition, ['left', 'right'], true) ? ($sidebarPosition === 'left' ? ($sidebarType === 'compact' ? 'lg:pl-20' : 'lg:pl-64') : ($sidebarType === 'compact' ? 'lg:pr-20' : 'lg:pr-64')) : ($sidebarPosition === 'top' ? 'pt-20' : 'pb-20')) : '' }} min-h-screen bg-[#fafaf8] text-zinc-950 antialiased" style="--finder-primary: {{ $accent }}">
@if($sidebarEnabled)
    @if(in_array($sidebarPosition, ['top', 'bottom'], true))
        <aside class="fixed {{ $sidebarPosition === 'top' ? 'top-0 border-b' : 'bottom-0 border-t' }} left-0 z-50 w-full border-zinc-200/80 bg-white/95 shadow-sm backdrop-blur-xl">
            <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-3 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:gap-6 lg:px-8">
                <a href="{{ route('site.public', [$site, 'pageSlug' => 'home', 'preview' => $preview ? 1 : null]) }}" class="flex min-w-0 items-center gap-3">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-xl text-sm font-black text-white shadow-sm" style="background:var(--finder-primary)">{{ mb_strtoupper(mb_substr($site->name, 0, 1)) }}</span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-bold tracking-tight text-zinc-950">{{ $site->name }}</span>
                        <span class="block text-[10px] font-semibold uppercase tracking-[.16em] text-zinc-400">Menu do site</span>
                    </span>
                </a>
                <nav class="-mx-1 flex items-center gap-1 overflow-x-auto px-1 pb-0.5 lg:mx-0 lg:justify-end lg:pb-0" aria-label="Navegação do website">
                    @foreach($sidebarPages as $sidebarPage)
                        <a href="{{ route('site.public', [$site, 'pageSlug' => $sidebarPage->slug, 'preview' => $preview ? 1 : null]) }}" title="{{ $sidebarPage->name }}" class="inline-flex shrink-0 items-center rounded-full px-4 py-2 text-sm font-semibold transition {{ $currentPage === $sidebarPage->slug ? 'text-white shadow-sm' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}" @if($currentPage === $sidebarPage->slug) style="background:var(--finder-primary)" @endif>
                            {{ $sidebarPage->name }}
                        </a>
                    @endforeach
                    <a href="{{ route('site.public', ['site' => $site, 'pageSlug' => 'login', 'preview' => $preview ? 1 : null]) }}" class="inline-flex shrink-0 items-center rounded-full border border-zinc-200 px-4 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100">Entrar</a>
                    <button type="button" wire:click="$set('cartOpen', true)" class="relative inline-flex shrink-0 items-center gap-2 rounded-full border border-zinc-200 bg-white px-4 py-2 text-sm font-bold text-zinc-800">
                        🛒 Carrinho
                        @if(count($cart)) <span class="rounded-full px-2 py-0.5 text-xs text-white" style="background:var(--finder-primary)">{{ array_sum($cart) }}</span> @endif
                    </button>
                </nav>
            </div>
        </aside>
    @else
        <aside x-data="{ sidebarOpen: true }" class="fixed inset-y-0 z-50 hidden {{ $sidebarAppearance === 'dark' ? 'border-zinc-800 bg-zinc-950 text-white' : 'border-zinc-200 bg-white text-zinc-950' }} shadow-sm lg:block {{ $sidebarPosition === 'right' ? 'right-0 border-l' : 'left-0 border-r' }} {{ $sidebarType === 'compact' ? 'w-20' : match ($sidebarWidth) { 'narrow' => 'w-48', 'wide' => 'w-80', default => 'w-64' } }}">
            @if($sidebarType === 'collapsible')
                <button type="button" @click="sidebarOpen = !sidebarOpen" class="m-4 flex items-center gap-2 rounded-xl bg-zinc-100 px-3 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-200">
                    <span class="text-lg">☰</span><span x-show="sidebarOpen">Recolher</span>
                </button>
            @else
                <div class="border-b border-zinc-100 px-5 py-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-zinc-400">Navegação</p>
                    <p class="mt-1 truncate font-bold text-zinc-900">{{ $site->name }}</p>
                </div>
            @endif
            <nav class="space-y-1 p-3" aria-label="Navegação lateral">
                @foreach($sidebarPages as $sidebarPage)
                    <a href="{{ route('site.public', [$site, 'pageSlug' => $sidebarPage->slug, 'preview' => $preview ? 1 : null]) }}" title="{{ $sidebarPage->name }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition {{ $currentPage === $sidebarPage->slug ? 'bg-zinc-900 text-white' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ $currentPage === $sidebarPage->slug ? 'bg-white/15' : 'bg-zinc-100' }}">@if($sidebarIcons === 'shown'){{ mb_strtoupper(mb_substr($sidebarPage->name, 0, 1)) }}@endif</span>
                        @if($sidebarType !== 'compact')
                            <span x-show="sidebarOpen" class="truncate">{{ $sidebarPage->name }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>
        </aside>
    @endif
@endif    @unless($sidebarEnabled && $sidebarPosition === 'top')
    <header class="sticky top-0 z-40 border-b border-black/5 bg-[#fafaf8]/90 backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-5 py-4 sm:px-8">
            <a href="{{ route('site.public', [$site, 'pageSlug' => 'home', 'preview' => $preview ? 1 : null]) }}" class="flex items-center gap-3">
                <span class="flex size-10 items-center justify-center rounded-2xl text-sm font-black text-white" style="background:var(--finder-primary)">{{ mb_strtoupper(mb_substr($site->name, 0, 1)) }}</span>
                <span class="font-black tracking-tight">{{ $site->name }}</span>
            </a>
            <nav class="hidden items-center gap-1 md:flex">
                @if($menuApplied && $activeMenu)
                    @foreach($menuItems as $item)
                        @if($item->page)
                            <a href="{{ route('site.public', [$site, 'pageSlug' => $item->page->slug, 'preview' => $preview ? 1 : null]) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ $currentPage === $item->page->slug ? 'bg-zinc-950 text-white' : 'text-zinc-600 hover:bg-white' }}">{{ $item->label }}</a>
                        @endif
                    @endforeach
                @else
                    @foreach($visiblePages as $sitePage)
                        <a href="{{ route('site.public', [$site, 'pageSlug' => $sitePage->slug, 'preview' => $preview ? 1 : null]) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ $currentPage === $sitePage->slug ? 'bg-zinc-950 text-white' : 'text-zinc-600 hover:bg-white' }}">{{ $labels[$sitePage->slug] ?? $sitePage->name }}</a>
                    @endforeach
                @endif
            </nav>
            <div class="flex items-center gap-2">
                <a href="{{ route('site.public', ['site' => $site, 'pageSlug' => 'login', 'preview' => $preview ? 1 : null]) }}" class="inline-flex items-center rounded-full border border-black/10 bg-white px-4 py-2.5 text-sm font-bold text-zinc-700 transition hover:bg-zinc-100">Entrar</a>
                <button type="button" wire:click="$set('cartOpen', true)" class="relative inline-flex items-center gap-2 rounded-full border border-black/10 bg-white px-4 py-2.5 text-sm font-bold">
                    🛒 Carrinho
                    @if(count($cart)) <span class="rounded-full px-2 py-0.5 text-xs text-white" style="background:var(--finder-primary)">{{ array_sum($cart) }}</span> @endif
                </button>
                <a href="{{ route('site.public', [$site, 'pageSlug' => 'products', 'preview' => $preview ? 1 : null]) }}" class="hidden rounded-full px-5 py-2.5 text-sm font-bold text-white sm:inline-flex" style="background:var(--finder-primary)">Ver produtos</a>
            </div>
        </div>
        <div class="overflow-x-auto border-t border-black/5 md:hidden">
            <nav class="mx-auto flex min-w-max gap-1 px-5 py-2">
                @foreach($visiblePages as $sitePage)
                    <a href="{{ route('site.public', [$site, 'pageSlug' => $sitePage->slug, 'preview' => $preview ? 1 : null]) }}" class="rounded-full px-3 py-1.5 text-xs font-semibold {{ $currentPage === $sitePage->slug ? 'bg-zinc-950 text-white' : 'text-zinc-500' }}">{{ $labels[$sitePage->slug] ?? $sitePage->name }}</a>
                @endforeach
            </nav>
        </div>
    </header>
    @endunless

    @if($preview)
        <div data-visual-ignore class="border-b border-amber-200 bg-amber-50 px-5 py-2.5 text-center text-xs font-semibold text-amber-800">Pré-visualização privada — a loja ainda não está publicada.</div>
    @endif

    @if(session('site_form_success'))
        <div class="mx-auto max-w-3xl px-5 pt-6"><div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('site_form_success') }}</div></div>
    @endif

    @if($currentPage === 'home')
        <main>
            <section class="relative overflow-hidden px-5 pb-20 pt-16 sm:px-8 sm:pb-28 sm:pt-24">
                <div class="absolute -right-40 -top-40 size-[30rem] rounded-full opacity-20 blur-3xl" style="background:var(--finder-primary)"></div>
                <div class="relative mx-auto grid max-w-7xl items-center gap-12 lg:grid-cols-[1.05fr_.95fr]">
                    <div>
                        <span class="inline-flex rounded-full border border-black/5 bg-white px-4 py-2 text-xs font-black uppercase tracking-[.18em] text-zinc-500">Loja online</span>
                        <h1 class="mt-6 max-w-3xl text-5xl font-black tracking-[-.05em] sm:text-7xl">{{ $headline !== '' ? $headline : $site->name }}</h1>
                        <p class="mt-6 max-w-2xl text-lg leading-8 text-zinc-600">{{ $about ?: 'Descobre a nossa coleção e encontra os produtos disponíveis na loja.' }}</p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="{{ route('site.public', [$site, 'pageSlug' => 'products', 'preview' => $preview ? 1 : null]) }}" class="rounded-full px-6 py-3.5 text-sm font-bold text-white" style="background:var(--finder-primary)">Explorar produtos →</a>
                            <a href="{{ route('site.public', [$site, 'pageSlug' => 'about', 'preview' => $preview ? 1 : null]) }}" class="rounded-full border border-black/10 bg-white px-6 py-3.5 text-sm font-bold">Conhecer a marca</a>
                        </div>
                    </div>
                    <div class="rounded-[2rem] bg-zinc-950 p-3 shadow-2xl">
                        <div class="flex min-h-[360px] flex-col justify-between rounded-[1.5rem] p-7 text-white" style="background:linear-gradient(135deg,var(--finder-primary),#111827)">
                            <div class="flex justify-between text-xs font-bold uppercase tracking-[.18em]"><span>{{ $site->name }}</span><span>{{ $productCount }} {{ $productCount === 1 ? 'produto' : 'produtos' }}</span></div>
                            <div><p class="text-sm text-white/60">Coleção</p><p class="mt-2 text-4xl font-black tracking-tight">Escolhe o que combina contigo.</p></div>
                        </div>
                    </div>
                </div>
            </section>
            <section class="border-y border-black/5 bg-white px-5 py-16 sm:px-8 sm:py-20">
                <div class="mx-auto max-w-7xl">
                    <div class="flex items-end justify-between gap-5"><div><p class="text-xs font-black uppercase tracking-[.18em]" style="color:var(--finder-primary)">Coleção</p><h2 class="mt-2 text-3xl font-black tracking-tight">Produtos em destaque</h2></div><a href="{{ route('site.public', [$site, 'pageSlug' => 'products', 'preview' => $preview ? 1 : null]) }}" class="text-sm font-bold" style="color:var(--finder-primary)">Ver catálogo →</a></div>
                    @include('livewire.partials.store-product-grid', ['products' => $site->products->where('is_active', true)->take(6), 'preview' => $preview, 'site' => $site])
                </div>
            </section>
        </main>
    @elseif($currentPage === 'products')
        <main>
            <section class="px-5 pb-12 pt-16 sm:px-8 sm:pt-24"><div class="mx-auto max-w-7xl"><p class="text-xs font-black uppercase tracking-[.18em]" style="color:var(--finder-primary)">Catálogo</p><h1 class="mt-3 text-5xl font-black tracking-[-.04em] sm:text-7xl">Produtos</h1><p class="mt-5 max-w-2xl text-lg leading-8 text-zinc-600">Explora os produtos disponíveis na loja.</p></div></section>
            <section class="bg-white px-5 py-10 sm:px-8 sm:py-16"><div class="mx-auto max-w-7xl">@include('livewire.partials.store-product-grid', ['products' => $site->products->where('is_active', true), 'preview' => $preview, 'site' => $site])</div></section>
        </main>
    @elseif($currentPage === 'about')
        <main><section class="px-5 py-20 sm:px-8 sm:py-32"><div class="mx-auto max-w-5xl"><p class="text-xs font-black uppercase tracking-[.18em]" style="color:var(--finder-primary)">Sobre a marca</p><h1 class="mt-4 text-5xl font-black tracking-[-.05em] sm:text-7xl">{{ $site->name }}</h1><p class="mt-8 max-w-3xl text-xl leading-9 text-zinc-600">{{ $about ?: 'Uma marca com uma história própria.' }}</p></div></section><section class="bg-white px-5 py-20 sm:px-8"><div class="mx-auto max-w-5xl"><h2 class="text-3xl font-black">A nossa história</h2><p class="mt-5 whitespace-pre-line text-lg leading-9 text-zinc-600">{{ $about ?: 'Conhece melhor a marca e a sua forma de trabalhar.' }}</p></div></section></main>
    @elseif($currentPage === 'contact')
        <main><section class="px-5 py-20 sm:px-8 sm:py-28"><div class="mx-auto grid max-w-6xl gap-12 lg:grid-cols-[.8fr_1.2fr]"><div><p class="text-xs font-black uppercase tracking-[.18em]" style="color:var(--finder-primary)">Apoio ao cliente</p><h1 class="mt-4 text-5xl font-black tracking-tight">Como podemos ajudar?</h1><p class="mt-6 text-lg leading-8 text-zinc-600">Entra em contacto para dúvidas sobre produtos ou encomendas.</p>@if($contactEmail !== '')<a href="mailto:{{ $contactEmail }}" class="mt-6 inline-block font-bold" style="color:var(--finder-primary)">{{ $contactEmail }}</a>@endif</div><div class="rounded-[2rem] bg-white p-6 shadow-xl shadow-zinc-900/5 sm:p-9"><form wire:submit="submitContact" class="grid gap-5"><input wire:model="contactName" type="text" required placeholder="O teu nome" class="rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3.5"><input wire:model="contactEmail" type="email" required placeholder="O teu email" class="rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3.5"><textarea wire:model="contactMessage" required rows="6" placeholder="A tua mensagem" class="resize-none rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3.5"></textarea><button type="submit" class="rounded-2xl px-5 py-3.5 text-sm font-bold text-white" style="background:var(--finder-primary)">Enviar mensagem</button></form></div></div></section></main>
    @elseif($currentPage === 'faq')
        <main><section class="px-5 py-20 sm:px-8 sm:py-28"><div class="mx-auto max-w-4xl"><p class="text-xs font-black uppercase tracking-[.18em]" style="color:var(--finder-primary)">Ajuda</p><h1 class="mt-4 text-5xl font-black">Perguntas frequentes</h1><div class="mt-12 space-y-4">@foreach($page->sections->where('type','faq') as $section) @foreach(($section->content['items'] ?? []) as $item)<details class="rounded-2xl border border-black/5 bg-white p-5"><summary class="cursor-pointer font-bold">{{ $item['question'] ?? '' }}</summary><p class="mt-3 text-sm leading-7 text-zinc-600">{{ $item['answer'] ?? '' }}</p></details>@endforeach @endforeach</div></div></section></main>
    @else
        <main class="px-5 py-20 sm:px-8">
            <div class="mx-auto max-w-7xl">
                <p class="text-xs font-black uppercase tracking-[.18em]" style="color:var(--finder-primary)">{{ $labels[$currentPage] ?? $page->name }}</p>
                <h1 class="mt-3 text-5xl font-black">{{ $page->name }}</h1>
                <div class="mt-10 space-y-8">
                    @foreach($page->sections->where('is_visible', true)->sortBy('sort_order') as $section)
                        @php($sectionContent = $section->content ?? [])
                        @php($sectionSettings = $section->settings ?? [])
                        @php($sectionBackground = data_get($sectionSettings, 'background_color', '#ffffff'))
                        @php($sectionText = data_get($sectionSettings, 'text_color', '#27272a'))
                        @php($sectionAccent = data_get($sectionSettings, 'accent_color', '#635bff'))
                        @php($sectionBorder = data_get($sectionSettings, 'border_color', '#e4e4e7'))
                        <section class="rounded-3xl border p-6 sm:p-8" style="background-color: {{ $sectionBackground }}; color: {{ $sectionText }}; border-color: {{ $sectionBorder }}">
                            @if(filled(data_get($sectionContent, 'title')))<h2 class="text-2xl font-black">{{ data_get($sectionContent, 'title') }}</h2>@endif
                            @if(filled(data_get($sectionContent, 'body', data_get($sectionContent, 'description', ''))))<p class="mt-3 whitespace-pre-line leading-8" style="color: {{ $sectionText }}">{{ data_get($sectionContent, 'body', data_get($sectionContent, 'description', '')) }}</p>@endif
                            @if(filled(data_get($sectionContent, 'image_url')))<img src="{{ data_get($sectionContent, 'image_url') }}" alt="{{ data_get($sectionContent, 'title', $section->label) }}" loading="lazy" class="mt-5 max-h-[34rem] w-full rounded-2xl object-cover">@endif
                            @if(count(data_get($sectionContent, 'items', [])))
                                <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach(data_get($sectionContent, 'items', []) as $sectionItem)
                                        <div class="rounded-2xl border p-5" style="background-color: {{ $sectionBackground }}; color: {{ $sectionText }}; border-color: {{ $sectionBorder }}"><h3 class="font-bold">{{ is_array($sectionItem) ? ($sectionItem['title'] ?? '') : $sectionItem }}</h3></div>
                                    @endforeach
                                </div>
                            @endif
                            @if(filled(data_get($sectionContent, 'button_label')) && filled(data_get($sectionContent, 'button_url')))<a href="{{ data_get($sectionContent, 'button_url') }}" class="mt-6 inline-flex rounded-xl px-5 py-3 text-sm font-bold text-white" style="background-color: {{ $sectionAccent }}">{{ data_get($sectionContent, 'button_label') }}</a>@endif
                        </section>
                    @endforeach
                </div>
            </div>
        </main>
    @endif

    @include('livewire.partials.page-custom-sections')

    @if($mediaByPlacement->isNotEmpty())
        <section class="border-t border-black/5 bg-white px-5 py-16 sm:px-8">
            <div class="mx-auto max-w-7xl"><p class="text-xs font-black uppercase tracking-[.18em]" style="color:var(--finder-primary)">Conteúdo visual</p><div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">@foreach($mediaByPlacement->flatten() as $item)<img src="{{ route('site.manage.media.file', ['media' => $item->id]) }}" alt="{{ $item->alt_text ?: $item->original_name }}" class="aspect-[4/3] w-full rounded-3xl object-cover">@endforeach</div></div>
        </section>
    @endif



    @if($completedOrderNumber)
        <div class="fixed inset-0 z-[80] flex items-center justify-center bg-black/50 p-5">
            <div class="w-full max-w-lg rounded-[2rem] bg-white p-8 text-center shadow-2xl">
                <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-emerald-100 text-2xl">✓</div>
                <h2 class="mt-5 text-3xl font-black">Encomenda recebida</h2>
                <p class="mt-3 text-zinc-600">A tua encomenda foi registada com sucesso.</p>
                <p class="mt-5 rounded-2xl bg-zinc-50 px-4 py-3 font-black">{{ $completedOrderNumber }}</p>
                <button type="button" wire:click="$set('completedOrderNumber', null)" class="mt-6 rounded-2xl px-6 py-3 font-bold text-white" style="background:var(--finder-primary)">Continuar</button>
            </div>
        </div>
    @endif

    @if($cartOpen)
        <div class="fixed inset-0 z-[70] bg-black/40" wire:click="$set('cartOpen', false)">
            <aside class="absolute right-0 top-0 h-full w-full max-w-md overflow-y-auto bg-white p-6 shadow-2xl" wire:click.stop>
                <div class="flex items-center justify-between">
                    <div><p class="text-xs font-black uppercase tracking-wider text-zinc-400">A tua compra</p><h2 class="mt-1 text-2xl font-black">Carrinho</h2></div>
                    <button type="button" wire:click="$set('cartOpen', false)" class="rounded-full bg-zinc-100 px-3 py-2 font-bold">✕</button>
                </div>

                @if($this->cartProducts->isEmpty())
                    <div class="py-20 text-center text-zinc-500">O teu carrinho está vazio.</div>
                @else
                    <div class="mt-8 space-y-4">
                        @foreach($this->cartProducts as $product)
                            <div class="rounded-2xl border border-zinc-200 p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div><p class="font-bold">{{ $product->name }}</p><p class="mt-1 text-sm text-zinc-500">{{ number_format((float)$product->price, 2, ',', '.') }} €</p></div>
                                    <button type="button" wire:click="removeFromCart({{ $product->id }})" class="text-xs font-bold text-red-600">Remover</button>
                                </div>
                                <div class="mt-4 flex items-center justify-between">
                                    <div class="flex items-center gap-2"><button type="button" wire:click="decreaseQuantity({{ $product->id }})" class="size-9 rounded-xl bg-zinc-100 font-bold">−</button><span class="w-8 text-center font-bold">{{ $cart[$product->id] ?? 0 }}</span><button type="button" wire:click="increaseQuantity({{ $product->id }})" class="size-9 rounded-xl bg-zinc-100 font-bold">+</button></div>
                                    <span class="font-black">{{ number_format((float)$product->price * ($cart[$product->id] ?? 0), 2, ',', '.') }} €</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-8 border-t border-zinc-200 pt-5">
                        <div class="flex justify-between text-sm"><span>Subtotal</span><strong>{{ number_format($this->cartSubtotal, 2, ',', '.') }} €</strong></div>
                        <div class="mt-2 flex justify-between text-sm"><span>IVA (23%)</span><strong>{{ number_format($this->cartTax, 2, ',', '.') }} €</strong></div>
                        <div class="mt-2 flex justify-between text-sm"><span>Envio</span><strong>{{ $this->cartShipping > 0 ? number_format($this->cartShipping, 2, ',', '.') . ' €' : 'Grátis' }}</strong></div>
                        <div class="mt-4 flex justify-between text-xl font-black"><span>Total</span><span>{{ number_format($this->cartTotal, 2, ',', '.') }} €</span></div>
                        <button type="button" wire:click="openCheckout" class="mt-6 w-full rounded-2xl px-5 py-4 font-bold text-white" style="background:var(--finder-primary)">Continuar para checkout</button>
                    </div>
                @endif
            </aside>
        </div>
    @endif

    @if($checkoutOpen)
        <div class="fixed inset-0 z-[75] overflow-y-auto bg-black/50 p-4 sm:p-8">
            <div class="mx-auto max-w-2xl rounded-[2rem] bg-white p-6 shadow-2xl sm:p-9">
                <div class="flex items-center justify-between"><div><p class="text-xs font-black uppercase tracking-wider text-zinc-400">Finalizar compra</p><h2 class="mt-1 text-3xl font-black">Checkout</h2></div><button type="button" wire:click="$set('checkoutOpen', false)" class="rounded-full bg-zinc-100 px-3 py-2 font-bold">✕</button></div>
                <form wire:submit="checkout" class="mt-8 grid gap-5">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <label class="grid gap-2 text-sm font-bold">Nome<input wire:model="customerName" required class="rounded-2xl border border-zinc-200 px-4 py-3"></label>
                        <label class="grid gap-2 text-sm font-bold">Email<input wire:model="customerEmail" type="email" required class="rounded-2xl border border-zinc-200 px-4 py-3"></label>
                    </div>
                    <label class="grid gap-2 text-sm font-bold">Telefone<input wire:model="customerPhone" required class="rounded-2xl border border-zinc-200 px-4 py-3"></label>
                    <label class="grid gap-2 text-sm font-bold">Morada de entrega<textarea wire:model="deliveryAddress" required rows="3" class="rounded-2xl border border-zinc-200 px-4 py-3"></textarea></label>
                    <label class="grid gap-2 text-sm font-bold">Método de pagamento<select wire:model="paymentMethod" class="rounded-2xl border border-zinc-200 px-4 py-3"><option value="mbway">MB WAY</option><option value="transferencia">Transferência bancária</option><option value="cartao">Cartão</option><option value="entrega">Pagamento na entrega</option></select></label>
                    <div class="rounded-2xl bg-zinc-50 p-5"><div class="flex justify-between text-sm"><span>Total</span><strong>{{ number_format($this->cartTotal, 2, ',', '.') }} €</strong></div><p class="mt-2 text-xs text-zinc-500">O pagamento fica registado como pendente até ser confirmado.</p></div>
                    <button type="submit" wire:loading.attr="disabled" class="rounded-2xl px-5 py-4 font-bold text-white" style="background:var(--finder-primary)">Confirmar encomenda</button>
                </form>
            </div>
        </div>
    @endif

    <footer class="border-t border-black/5 bg-zinc-950 px-5 py-10 text-white sm:px-8"><div class="mx-auto flex max-w-7xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><p class="font-bold">{{ $site->name }}</p><p class="text-xs text-white/40">© {{ now()->year }} · Loja online criada com Finder</p></div></footer>

@if(true)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const edits = new Map();
    const saved = @json(data_get($page->theme, 'visual_edits', []));
    const nodes = Array.from(document.querySelectorAll('h1,h2,h3,h4,p,a,button,span,img,section,header,footer,nav,main,article,li,small,strong,em,label,div')).filter(el => !el.closest('[data-visual-ignore]'));
    nodes.forEach(function (el, index) {
        el.dataset.visualKey = 'v' + index;
        const value = saved[el.dataset.visualKey];
        if (value) {
            if (value.text && el.children.length === 0) el.textContent = value.text;
            if (value.src && el.tagName === 'IMG') el.src = value.src;
            if (value.href && el.tagName === 'A') el.href = value.href;
            if (value.color) el.style.color = value.color;
            if (value.background) el.style.backgroundColor = value.background;
        }
    });
    if (!@json($preview && request()->boolean('visual_edit'))) return;
    const bar = document.createElement('div');
    bar.style.cssText = 'position:fixed;top:auto;right:auto;bottom:18px;left:50%;transform:translateX(-50%);z-index:999999;background:#18181b;color:white;padding:12px 14px;display:flex;gap:8px;align-items:center;justify-content:center;flex-wrap:wrap;max-width:calc(100vw - 24px);box-sizing:border-box;border:1px solid rgba(255,255,255,.12);box-shadow:0 12px 36px rgba(0,0,0,.3);border-radius:14px;font:600 13px system-ui';
    bar.innerHTML = '<b>Editar website</b><button id="visual-color" style="background:#3f3f46;padding:8px;border-radius:8px">Cores</button><button id="visual-save" style="background:#4f46e5;padding:8px;border-radius:8px">Guardar</button><span id="visual-status"></span>';
    document.body.appendChild(bar);
    let selected = null;
    nodes.forEach(function (el) {
        el.style.cursor = 'pointer';
        el.addEventListener('click', function (event) {
            if (event.target.closest('#visual-save, #visual-color')) return;
            event.preventDefault(); event.stopPropagation(); selected = el;
            if (el.tagName === 'IMG') {
                const value = prompt('URL da imagem', el.src);
                if (value) { el.src = value; edits.set(el.dataset.visualKey, {key:el.dataset.visualKey,src:value}); }
            } else if (el.tagName === 'A') {
                if (el.children.length === 0) {
                    const value = prompt('Texto', el.innerText);
                    if (value !== null) { el.textContent = value; edits.set(el.dataset.visualKey, {...(edits.get(el.dataset.visualKey)||{}),key:el.dataset.visualKey,text:value}); }
                }
                const href = prompt('Destino do link', el.getAttribute('href') || '');
                if (href !== null) { el.setAttribute('href',href); edits.set(el.dataset.visualKey, {...(edits.get(el.dataset.visualKey)||{}),key:el.dataset.visualKey,href:href}); }
            } else if (el.children.length === 0) {
                el.contentEditable = 'true'; el.focus();
                el.onblur = function () { el.contentEditable = 'false'; edits.set(el.dataset.visualKey, {...(edits.get(el.dataset.visualKey)||{}),key:el.dataset.visualKey,text:el.innerText}); };
            }
        }, false);
    });
    document.getElementById('visual-color').onclick = function () {
        if (!selected) return;
        const color = prompt('Cor do texto (#hex)', '#27272a');
        if (color && /^#[0-9a-fA-F]{6}$/.test(color)) {
            selected.style.color = color;
            edits.set(selected.dataset.visualKey, {...(edits.get(selected.dataset.visualKey)||{}),key:selected.dataset.visualKey,color:color});
        }
        const background = prompt('Cor de fundo (#hex)', '');
        if (background && /^#[0-9a-fA-F]{6}$/.test(background)) {
            selected.style.backgroundColor = background;
            edits.set(selected.dataset.visualKey, {...(edits.get(selected.dataset.visualKey)||{}),key:selected.dataset.visualKey,background:background});
        }
    };
    document.getElementById('visual-save').onclick = async function () {
        const status = document.getElementById('visual-status');
        if (!edits.size) { status.textContent = 'Sem alterações'; return; }
        status.textContent = 'A guardar…';
        try {
            const response = await fetch(@json(route('site.manage.pages.visual.save', $page)), {
                method: 'POST', credentials: 'same-origin',
                headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':@json(csrf_token())},
                body: JSON.stringify({edits:Array.from(edits.values())})
            });
            if (!response.ok) throw new Error('Erro ' + response.status);
            edits.clear(); status.textContent = 'Guardado ✓';
        } catch (error) { status.textContent = 'Não foi possível guardar'; }
    };
});
</script>
@endif

</div>
