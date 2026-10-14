@if(in_array($page->slug, ['home', 'about', 'contact'], true) && $page->sections->where('is_visible', true)->isNotEmpty())
    <div class="space-y-0">
        @foreach($page->sections->where('is_visible', true)->sortBy('sort_order') as $section)
            @php($content = $section->content ?? [])
            <section class="border-t border-black/5 bg-white px-5 py-14 sm:px-8 sm:py-20">
                <div class="mx-auto max-w-6xl">
                    @if(filled(data_get($content, 'title')))
                        <h2 class="text-3xl font-black tracking-tight sm:text-4xl">{{ data_get($content, 'title') }}</h2>
                    @endif
                    @if(filled(data_get($content, 'body')))
                        <p class="mt-4 max-w-3xl whitespace-pre-line text-base leading-8 text-zinc-600">{{ data_get($content, 'body') }}</p>
                    @endif
                    @if(filled(data_get($content, 'image_url')))
                        <img src="{{ data_get($content, 'image_url') }}" alt="{{ data_get($content, 'title', $section->label) }}" loading="lazy" class="mt-6 max-h-[34rem] w-full rounded-3xl object-cover">
                    @endif
                    @if(in_array($section->type, ['features', 'gallery', 'testimonials', 'faq', 'widget'], true) && count(data_get($content, 'items', [])))
                        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach(data_get($content, 'items', []) as $item)
                                <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-5">
                                    <h3 class="font-bold">{{ is_array($item) ? ($item['title'] ?? '') : $item }}</h3>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @if(filled(data_get($content, 'button_label')) && filled(data_get($content, 'button_url')))
                        <a href="{{ data_get($content, 'button_url') }}" class="mt-6 inline-flex rounded-xl px-5 py-3 text-sm font-bold text-white" style="background:var(--finder-primary)">{{ data_get($content, 'button_label') }}</a>
                    @endif
                </div>
            </section>
        @endforeach
    </div>
@endif