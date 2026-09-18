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
    $currentPage = $page->slug;
    $menuApplied = filled(data_get($site->settings, 'menu_applied_at'));
    $activeMenu = $menuApplied ? $site->menus->firstWhere('location', 'header') : null;
    $menuItems = $activeMenu?->items?->where('is_visible', true)->whereNull('parent_id')->values() ?? collect();
    $labels = ['home' => 'Início', 'products' => 'Produtos', 'about' => 'Sobre a marca', 'contact' => 'Contactos', 'faq' => 'FAQ', 'gallery' => 'Galeria', 'blog' => 'Blog'];
    $visiblePages = $site->pages->sortBy('sort_order')->whereIn('slug', array_keys($labels));
    $productCount = $site->products->count();
@endphp

<div class="min-h-screen bg-[#fafaf8] text-zinc-950 antialiased" style="--finder-primary: {{ $accent }}">
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
            <a href="{{ route('site.public', [$site, 'pageSlug' => 'products', 'preview' => $preview ? 1 : null]) }}" class="hidden rounded-full px-5 py-2.5 text-sm font-bold text-white sm:inline-flex" style="background:var(--finder-primary)">Ver produtos</a>
        </div>
        <div class="overflow-x-auto border-t border-black/5 md:hidden">
            <nav class="mx-auto flex min-w-max gap-1 px-5 py-2">
                @foreach($visiblePages as $sitePage)
                    <a href="{{ route('site.public', [$site, 'pageSlug' => $sitePage->slug, 'preview' => $preview ? 1 : null]) }}" class="rounded-full px-3 py-1.5 text-xs font-semibold {{ $currentPage === $sitePage->slug ? 'bg-zinc-950 text-white' : 'text-zinc-500' }}">{{ $labels[$sitePage->slug] ?? $sitePage->name }}</a>
                @endforeach
            </nav>
        </div>
    </header>

    @if($preview)
        <div class="border-b border-amber-200 bg-amber-50 px-5 py-2.5 text-center text-xs font-semibold text-amber-800">Pré-visualização privada — a loja ainda não está publicada.</div>
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
        <main class="px-5 py-20 sm:px-8"><div class="mx-auto max-w-7xl"><p class="text-xs font-black uppercase tracking-[.18em]" style="color:var(--finder-primary)">{{ $labels[$currentPage] ?? $page->name }}</p><h1 class="mt-3 text-5xl font-black">{{ $page->name }}</h1><div class="mt-10 space-y-8">@foreach($page->sections as $section)<section class="rounded-3xl bg-white p-8"><h2 class="text-2xl font-black">{{ data_get($section->content,'title','') }}</h2><p class="mt-3 whitespace-pre-line leading-8 text-zinc-600">{{ data_get($section->content,'body',data_get($section->content,'description','')) }}</p></section>@endforeach</div></div></main>
    @endif

    @if($mediaByPlacement->isNotEmpty())
        <section class="border-t border-black/5 bg-white px-5 py-16 sm:px-8">
            <div class="mx-auto max-w-7xl"><p class="text-xs font-black uppercase tracking-[.18em]" style="color:var(--finder-primary)">Conteúdo visual</p><div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">@foreach($mediaByPlacement->flatten() as $item)<img src="{{ route('site.manage.media.file', ['media' => $item->id]) }}" alt="{{ $item->alt_text ?: $item->original_name }}" class="aspect-[4/3] w-full rounded-3xl object-cover">@endforeach</div></div>
        </section>
    @endif

    <footer class="border-t border-black/5 bg-zinc-950 px-5 py-10 text-white sm:px-8"><div class="mx-auto flex max-w-7xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><p class="font-bold">{{ $site->name }}</p><p class="text-xs text-white/40">© {{ now()->year }} · Loja online criada com Finder</p></div></footer>
</div>
