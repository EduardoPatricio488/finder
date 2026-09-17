<div
    style="--finder-primary: {{ $site->theme['primary'] ?? $site->primary_color ?? '#635bff' }}; --finder-secondary: {{ $site->theme['secondary'] ?? '#111827' }}; --finder-bg: {{ $site->theme['background'] ?? '#ffffff' }}; --finder-text: {{ $site->theme['text'] ?? '#111827' }}; --finder-radius: {{ $site->theme['radius'] ?? '1rem' }}"
    class="min-h-screen bg-[var(--finder-bg)] text-[var(--finder-text)]"
>
    @php($menu = $site->menus->firstWhere('location', 'header'))
    @php($modelLayout = $site->theme['layout'] ?? 'top')
    @php($sitePages = $site->pages->sortBy('sort_order'))

    @if(in_array($modelLayout, ['sidebar-left', 'sidebar-right'], true))
        <div class="min-h-screen md:flex {{ $modelLayout === 'sidebar-right' ? 'md:flex-row-reverse' : '' }}">
            <aside class="w-full shrink-0 border-black/5 bg-white/95 p-5 shadow-sm md:sticky md:top-0 md:h-screen md:w-64 md:border-r md:p-6 {{ $modelLayout === 'sidebar-right' ? 'md:border-l md:border-r-0' : '' }}">
                <div class="flex items-center gap-3 border-b border-black/5 pb-5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-zinc-950 text-sm font-black text-white">{{ mb_strtoupper(mb_substr($site->name, 0, 1)) }}</div>
                    <a href="{{ route('site.public', $site) }}" class="min-w-0 truncate font-bold tracking-tight">{{ $site->name }}</a>
                </div>
                <nav class="mt-6 space-y-1" aria-label="Navegação principal">
                    @foreach($sitePages as $sitePage)
                        <a href="{{ route('site.public', [$site, 'pageSlug' => $sitePage->slug]) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ $page->id === $sitePage->id ? 'bg-zinc-950 text-white' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg {{ $page->id === $sitePage->id ? 'bg-white/10' : 'bg-zinc-100' }}">{{ $sitePage->is_homepage ? '⌂' : '○' }}</span>
                            <span class="truncate">{{ $sitePage->name }}</span>
                        </a>
                    @endforeach
                </nav>
            </aside>
            <div class="min-w-0 flex-1">
    @elseif($modelLayout === 'bottom')
        <header class="sticky top-0 z-30 border-b border-black/5 bg-white/85 backdrop-blur dark:bg-zinc-950/85">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-5 py-4">
                <a href="{{ route('site.public', $site) }}" class="shrink-0 font-semibold tracking-tight">{{ $site->name }}</a>
                <a href="{{ route('site.public', $site) }}" class="rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-sm" style="background: var(--finder-primary)">Início</a>
            </div>
        </header>
    @else
        <header class="sticky top-0 z-30 border-b border-black/5 bg-white/85 backdrop-blur dark:bg-zinc-950/85">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-5 py-4">
                <a href="{{ route('site.public', $site) }}" class="shrink-0 font-semibold tracking-tight">{{ $site->name }}</a>
                @if($modelLayout === 'top' && $menu)
                    <nav class="hidden items-center gap-6 md:flex" aria-label="Navegação principal">
                        @foreach($menu->items->where('is_visible', true)->whereNull('parent_id') as $item)
                            @php($itemUrl = $item->site_page_id && $item->page ? route('site.public', [$site, 'pageSlug' => $item->page->slug]) : $item->url)
                            <a href="{{ $itemUrl }}" target="{{ $item->target }}" @if($item->target === '_blank') rel="noopener noreferrer" @endif class="text-sm text-zinc-600 transition hover:text-zinc-950 dark:text-zinc-300 dark:hover:text-white">{{ $item->label }}</a>
                        @endforeach
                    </nav>
                @endif
                <a href="{{ route('site.public', $site) }}" class="rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-sm" style="background: var(--finder-primary)">Início</a>
            </div>
        </header>
    @endif

    @if($preview)
        <div class="border-b border-amber-200 bg-amber-50 px-5 py-2 text-center text-xs font-medium text-amber-800">Pré-visualização privada — este conteúdo não está público.</div>
    @endif

    @if(session('site_form_success'))
        <div class="mx-auto max-w-3xl px-5 pt-6" role="status"><div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('site_form_success') }}</div></div>
    @endif
    @if(session('site_newsletter_success'))
        <div class="mx-auto max-w-3xl px-5 pt-6" role="status"><div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('site_newsletter_success') }}</div></div>
    @endif

    <main>
        @foreach($page->sections->where('is_visible', true) as $section)
            @php($content = $section->content ?? [])
            @switch($section->type)
                @case('hero')
                    <section class="px-5 py-24 text-center sm:py-32"><div class="mx-auto max-w-4xl"><h1 class="text-4xl font-bold tracking-tight sm:text-6xl">{{ $content['title'] ?? $site->name }}</h1><p class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-zinc-600 dark:text-zinc-300">{{ $content['subtitle'] ?? $site->tagline }}</p>@if(!empty($content['button_label']))<a href="{{ $content['button_url'] ?? '#' }}" class="mt-8 inline-flex rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-sm" style="background: var(--finder-primary)">{{ $content['button_label'] }}</a>@endif</div></section>
                    @break
                @case('text')
                    <section class="mx-auto max-w-3xl px-5 py-16">@if(!empty($content['title']))<h2 class="text-3xl font-semibold tracking-tight">{{ $content['title'] }}</h2>@endif<p class="mt-4 whitespace-pre-line leading-8 text-zinc-600 dark:text-zinc-300">{{ $content['body'] ?? $content['description'] ?? '' }}</p></section>
                    @break
                @case('image')
                    <section class="mx-auto max-w-6xl px-5 py-16">@if(!empty($content['url']))<figure><img src="{{ $content['url'] }}" alt="{{ $content['alt'] ?? '' }}" loading="lazy" class="mx-auto max-h-[700px] w-full rounded-3xl object-cover shadow-sm">@if(!empty($content['caption']))<figcaption class="mt-3 text-center text-sm text-zinc-500">{{ $content['caption'] }}</figcaption>@endif</figure>@endif</section>
                    @break
                @case('button')
                    <section class="px-5 py-12 text-center"><a href="{{ $content['url'] ?? '#' }}" target="{{ $content['target'] ?? '_self' }}" @if(($content['target'] ?? '_self') === '_blank') rel="noopener noreferrer" @endif class="inline-flex rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-sm" style="background: var(--finder-primary)">{{ $content['label'] ?? 'Saber mais' }}</a></section>
                    @break
                @case('card')
                    <section class="mx-auto max-w-5xl px-5 py-12"><article class="rounded-3xl border border-zinc-200 p-8 shadow-sm dark:border-zinc-800"><h2 class="text-2xl font-semibold">{{ $content['title'] ?? '' }}</h2><p class="mt-3 leading-7 text-zinc-600 dark:text-zinc-300">{{ $content['description'] ?? '' }}</p></article></section>
                    @break
                @case('feature_grid')
                    <section class="mx-auto max-w-6xl px-5 py-16"><h2 class="text-center text-3xl font-semibold tracking-tight">{{ $content['title'] ?? 'Destaques' }}</h2><div class="mt-10 grid gap-5 md:grid-cols-3">@foreach($content['items'] ?? [] as $item)<article class="rounded-2xl border border-zinc-200 p-6 shadow-sm dark:border-zinc-800"><h3 class="font-semibold">{{ $item['title'] ?? '' }}</h3><p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $item['description'] ?? '' }}</p></article>@endforeach</div></section>
                    @break
                @case('testimonials')
                    <section class="mx-auto max-w-6xl px-5 py-16"><h2 class="text-center text-3xl font-semibold">{{ $content['title'] ?? 'O que dizem' }}</h2><div class="mt-10 grid gap-5 md:grid-cols-3">@foreach($content['items'] ?? [] as $item)<blockquote class="rounded-2xl border border-zinc-200 p-6 dark:border-zinc-800"><p class="leading-7">“{{ $item['quote'] ?? $item['description'] ?? '' }}”</p><footer class="mt-4 text-sm font-semibold text-zinc-500">{{ $item['name'] ?? '' }}</footer></blockquote>@endforeach</div></section>
                    @break
                @case('faq')
                    <section class="mx-auto max-w-3xl px-5 py-16"><h2 class="text-3xl font-semibold">{{ $content['title'] ?? 'Perguntas frequentes' }}</h2><div class="mt-8 divide-y divide-zinc-200 dark:divide-zinc-800">@foreach($content['items'] ?? [] as $item)<details class="py-5"><summary class="cursor-pointer font-semibold">{{ $item['question'] ?? $item['title'] ?? '' }}</summary><p class="mt-3 leading-7 text-zinc-600 dark:text-zinc-300">{{ $item['answer'] ?? $item['description'] ?? '' }}</p></details>@endforeach</div></section>
                    @break
                @case('gallery')
                    <section class="mx-auto max-w-6xl px-5 py-16"><div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">@foreach($content['items'] ?? [] as $item)@if(!empty($item['url']))<img src="{{ $item['url'] }}" alt="{{ $item['alt'] ?? '' }}" loading="lazy" class="aspect-[4/3] w-full rounded-2xl object-cover">@endif @endforeach</div></section>
                    @break
                @case('video')
                    <section class="mx-auto max-w-5xl px-5 py-16">@if(!empty($content['url']))<div class="aspect-video overflow-hidden rounded-3xl bg-black"><iframe src="{{ $content['url'] }}" title="{{ $content['title'] ?? 'Vídeo' }}" class="h-full w-full" loading="lazy" allowfullscreen></iframe></div>@endif</section>
                    @break
                @case('product_grid')
                    <section class="mx-auto max-w-6xl px-5 py-16"><h2 class="text-3xl font-semibold tracking-tight">{{ $content['title'] ?? 'Produtos' }}</h2><div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">@forelse($site->products->where('is_active', true) as $product)<article class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">@if($product->image_url)<img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" class="aspect-square w-full object-cover">@endif<div class="p-5"><h3 class="font-semibold">{{ $product->name }}</h3><p class="mt-2 font-medium">{{ number_format((float) $product->price, 2, ',', '.') }} €</p>@if($product->description)<p class="mt-2 text-sm leading-6 text-zinc-500">{{ $product->description }}</p>@endif</div></article>@empty<p class="text-sm text-zinc-500">Não existem produtos publicados neste website.</p>@endforelse</div></section>
                    @break
                @case('pricing')
                    <section class="mx-auto max-w-6xl px-5 py-16"><h2 class="text-center text-3xl font-semibold">{{ $content['title'] ?? 'Planos' }}</h2><div class="mt-10 grid gap-5 md:grid-cols-3">@foreach($content['items'] ?? [] as $item)<article class="rounded-3xl border border-zinc-200 p-7 shadow-sm dark:border-zinc-800"><h3 class="text-xl font-semibold">{{ $item['name'] ?? $item['title'] ?? '' }}</h3><p class="mt-3 text-3xl font-bold">{{ $item['price'] ?? '' }}</p><p class="mt-3 text-sm text-zinc-500">{{ $item['description'] ?? '' }}</p></article>@endforeach</div></section>
                    @break
                @case('social_links')
                    <section class="px-5 py-12 text-center"><div class="flex flex-wrap justify-center gap-3">@foreach($content['items'] ?? [] as $item)<a href="{{ $item['url'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="rounded-xl border border-zinc-200 px-4 py-2 text-sm font-medium transition hover:-translate-y-0.5 dark:border-zinc-800">{{ $item['label'] ?? $item['name'] ?? 'Social' }}</a>@endforeach</div></section>
                    @break
                @case('map')
                    <section class="mx-auto max-w-6xl px-5 py-16">@if(!empty($content['embed_url']))<div class="aspect-video overflow-hidden rounded-3xl border border-zinc-200 dark:border-zinc-800"><iframe src="{{ $content['embed_url'] }}" title="Mapa" class="h-full w-full" loading="lazy"></iframe></div>@elseif(!empty($content['address']))<div class="rounded-2xl border border-zinc-200 p-10 text-center text-sm text-zinc-600 dark:border-zinc-800 dark:text-zinc-300">{{ $content['address'] }}</div>@endif</section>
                    @break
                @case('contact_form')
                    <section class="mx-auto max-w-2xl px-5 py-16"><div class="rounded-3xl border border-zinc-200 p-7 shadow-sm dark:border-zinc-800"><h2 class="text-2xl font-semibold">{{ $content['title'] ?? 'Contacta-nos' }}</h2><p class="mt-2 text-sm text-zinc-500">{{ $content['description'] ?? 'Envia-nos uma mensagem.' }}</p><form wire:submit="submitContact" class="mt-6 grid gap-4"><div><label for="contact-name" class="sr-only">Nome</label><input id="contact-name" wire:model="contactName" type="text" autocomplete="name" placeholder="Nome" class="w-full rounded-xl border border-zinc-200 px-4 py-3 outline-none"></div><div><label for="contact-email" class="sr-only">Email</label><input id="contact-email" wire:model="contactEmail" type="email" autocomplete="email" placeholder="Email" class="w-full rounded-xl border border-zinc-200 px-4 py-3 outline-none"></div><div><label for="contact-message" class="sr-only">Mensagem</label><textarea id="contact-message" wire:model="contactMessage" rows="5" placeholder="Mensagem" class="w-full rounded-xl border border-zinc-200 px-4 py-3 outline-none"></textarea></div>@error('contactName')<p class="text-sm text-red-600">{{ $message }}</p>@enderror @error('contactEmail')<p class="text-sm text-red-600">{{ $message }}</p>@enderror @error('contactMessage')<p class="text-sm text-red-600">{{ $message }}</p>@enderror<button type="submit" wire:loading.attr="disabled" class="rounded-xl px-5 py-3 text-sm font-semibold text-white disabled:opacity-60" style="background:var(--finder-primary)">Enviar mensagem</button></form></div></section>
                    @break
                @case('newsletter')
                    <section class="mx-auto max-w-3xl px-5 py-16"><div class="rounded-3xl p-8 text-center" style="background:color-mix(in srgb,var(--finder-primary) 10%,transparent)"><h2 class="text-2xl font-semibold">{{ $content['title'] ?? 'Recebe novidades' }}</h2><form wire:submit="subscribeNewsletter" class="mx-auto mt-6 flex max-w-lg gap-2"><label for="newsletter-email" class="sr-only">Email</label><input id="newsletter-email" wire:model="newsletterEmail" type="email" autocomplete="email" placeholder="O teu email" class="min-w-0 flex-1 rounded-xl border border-zinc-200 px-4 py-3 outline-none"><button type="submit" wire:loading.attr="disabled" class="rounded-xl px-5 py-3 text-sm font-semibold text-white disabled:opacity-60" style="background:var(--finder-primary)">Subscrever</button></form>@error('newsletterEmail')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror</div></section>
                    @break
                @case('blog_posts')
                    <section class="mx-auto max-w-6xl px-5 py-16"><h2 class="text-3xl font-semibold">{{ $content['title'] ?? 'Artigos' }}</h2><div class="mt-8 grid gap-5 md:grid-cols-3">@foreach($content['items'] ?? [] as $item)<article class="rounded-2xl border border-zinc-200 p-6 shadow-sm dark:border-zinc-800"><h3 class="font-semibold">{{ $item['title'] ?? '' }}</h3><p class="mt-2 text-sm leading-6 text-zinc-500">{{ $item['excerpt'] ?? $item['description'] ?? '' }}</p></article>@endforeach</div></section>
                    @break
                @case('cta')
                    <section class="mx-auto max-w-5xl px-5 py-16"><div class="rounded-3xl bg-zinc-950 px-6 py-14 text-center text-white sm:px-12"><h2 class="text-3xl font-bold">{{ $content['title'] ?? '' }}</h2><p class="mx-auto mt-3 max-w-2xl text-zinc-300">{{ $content['description'] ?? '' }}</p>@if(!empty($content['button_label']))<a href="{{ $content['button_url'] ?? '#' }}" class="mt-7 inline-flex rounded-xl bg-white px-5 py-3 text-sm font-semibold text-zinc-950 transition hover:-translate-y-0.5">{{ $content['button_label'] }}</a>@endif</div></section>
                    @break
                @default
                    <section class="mx-auto max-w-5xl px-5 py-16"><div class="rounded-2xl border border-dashed border-zinc-300 p-10 text-center"><h2 class="font-semibold">{{ $content['title'] ?? \Illuminate\Support\Str::headline($section->type) }}</h2><p class="mt-2 text-sm text-zinc-500">{{ $content['description'] ?? '' }}</p></div></section>
            @endswitch
        @endforeach
    </main>

    @if($modelLayout === 'bottom')
        <nav class="border-t border-black/5 bg-white px-5 py-5" aria-label="Navegação principal">
            <div class="mx-auto flex max-w-7xl flex-wrap justify-center gap-2">
                @foreach($sitePages as $sitePage)
                    <a href="{{ route('site.public', [$site, 'pageSlug' => $sitePage->slug]) }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold transition {{ $page->id === $sitePage->id ? 'bg-zinc-950 text-white' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">{{ $sitePage->name }}</a>
                @endforeach
            </div>
        </nav>
    @endif

    @if(in_array($modelLayout, ['sidebar-left', 'sidebar-right'], true))
            </div>
        </div>
    @endif

    <footer class="border-t border-black/5 px-5 py-10 dark:border-white/10">
        <div class="mx-auto flex max-w-7xl flex-col gap-3 text-sm text-zinc-500 sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ now()->year }} {{ $site->name }}</p>
            <a href="{{ route('site.public', $site) }}" class="font-medium transition hover:text-zinc-950 dark:hover:text-white">Início</a>
        </div>
    </footer>
</div>
