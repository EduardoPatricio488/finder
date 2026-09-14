<div style="--finder-primary: {{ $site->theme['primary'] ?? $site->primary_color ?? '#635bff' }}; --finder-secondary: {{ $site->theme['secondary'] ?? $site->secondary_color ?? '#111827' }}; --finder-bg: {{ $site->theme['background'] ?? '#ffffff' }}; --finder-text: {{ $site->theme['text'] ?? '#111827' }}" class="min-h-screen bg-[var(--finder-bg)] text-[var(--finder-text)]">
    @php($menu = $site->menus->firstWhere('location', 'header'))
    <header class="sticky top-0 z-10 border-b border-black/5 bg-white/80 backdrop-blur dark:bg-zinc-950/80">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4">
            <a href="{{ route('site.public', $site) }}" class="font-semibold tracking-tight">{{ $site->name }}</a>
            @if($menu)
                <nav class="hidden items-center gap-6 md:flex" aria-label="Navegação principal">
                    @foreach($menu->items->where('is_visible', true)->whereNull('parent_id') as $item)
                        <a href="{{ $item->site_page_id ? route('site.public', [$site, 'pageSlug' => $item->page?->slug]) : $item->url }}" target="{{ $item->target }}" class="text-sm text-zinc-600 transition hover:text-zinc-950 dark:text-zinc-300 dark:hover:text-white">{{ $item->label }}</a>
                    @endforeach
                </nav>
            @endif
            <a href="{{ route('site.public', $site) }}" class="rounded-xl px-4 py-2 text-sm font-medium text-white" style="background: var(--finder-primary)">Visitar</a>
        </div>
    </header>

    <main>
        @foreach($page->sections->where('is_visible', true) as $section)
            @php($content = $section->content ?? [])
            @switch($section->type)
                @case('hero')
                    <section class="px-5 py-24 text-center sm:py-32">
                        <div class="mx-auto max-w-4xl">
                            <h1 class="text-4xl font-bold tracking-tight sm:text-6xl">{{ $content['title'] ?? $site->name }}</h1>
                            <p class="mx-auto mt-6 max-w-2xl text-lg text-zinc-600 dark:text-zinc-300">{{ $content['subtitle'] ?? $site->tagline }}</p>
                            @if(!empty($content['button_label']))
                                <a href="{{ $content['button_url'] ?? '#' }}" class="mt-8 inline-flex rounded-xl px-5 py-3 text-sm font-semibold text-white" style="background: var(--finder-primary)">{{ $content['button_label'] }}</a>
                            @endif
                        </div>
                    </section>
                    @break
                @case('text')
                    <section class="mx-auto max-w-3xl px-5 py-16">
                        <h2 class="text-3xl font-semibold">{{ $content['title'] ?? '' }}</h2>
                        <p class="mt-4 whitespace-pre-line leading-7 text-zinc-600 dark:text-zinc-300">{{ $content['body'] ?? $content['description'] ?? '' }}</p>
                    </section>
                    @break
                @case('feature_grid')
                    <section class="mx-auto max-w-6xl px-5 py-16">
                        <h2 class="text-center text-3xl font-semibold">{{ $content['title'] ?? 'Destaques' }}</h2>
                        <div class="mt-10 grid gap-5 md:grid-cols-3">
                            @foreach($content['items'] ?? [] as $item)
                                <article class="rounded-2xl border border-zinc-200 p-6 dark:border-zinc-800">
                                    <h3 class="font-semibold">{{ $item['title'] ?? '' }}</h3>
                                    <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $item['description'] ?? '' }}</p>
                                </article>
                            @endforeach
                        </div>
                    </section>
                    @break
                @case('product_grid')
                    <section class="mx-auto max-w-6xl px-5 py-16">
                        <h2 class="text-3xl font-semibold">Produtos</h2>
                        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach($site->products->where('is_active', true) as $product)
                                <article class="overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-800">
                                    @if($product->image_url)<img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="aspect-square w-full object-cover">@endif
                                    <div class="p-5"><h3 class="font-semibold">{{ $product->name }}</h3><p class="mt-2 font-medium">{{ number_format((float) $product->price, 2, ',', '.') }} €</p></div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                    @break
                @case('cta')
                    <section class="mx-auto max-w-5xl px-5 py-16">
                        <div class="rounded-3xl bg-zinc-950 px-6 py-14 text-center text-white sm:px-12">
                            <h2 class="text-3xl font-bold">{{ $content['title'] ?? '' }}</h2>
                            <p class="mx-auto mt-3 max-w-2xl text-zinc-300">{{ $content['description'] ?? '' }}</p>
                            @if(!empty($content['button_label']))<a href="#" class="mt-7 inline-flex rounded-xl bg-white px-5 py-3 text-sm font-semibold text-zinc-950">{{ $content['button_label'] }}</a>@endif
                        </div>
                    </section>
                    @break
                @default
                    <section class="mx-auto max-w-5xl px-5 py-16"><div class="rounded-2xl border border-dashed border-zinc-300 p-10 text-center"><h2 class="font-semibold">{{ $content['title'] ?? \Illuminate\Support\Str::headline($section->type) }}</h2><p class="mt-2 text-sm text-zinc-500">{{ $content['description'] ?? '' }}</p></div></section>
            @endswitch
        @endforeach
    </main>

    <footer class="border-t border-black/5 px-5 py-10">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 text-sm text-zinc-500 sm:flex-row sm:items-center sm:justify-between">
            <span>{{ $site->name }}</span><span>Powered by Finder</span>
        </div>
    </footer>
</div>
