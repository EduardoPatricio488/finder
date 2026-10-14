@if(in_array($page->slug, ['home', 'about', 'contact'], true) && $page->sections->where('is_visible', true)->isNotEmpty())
    <div class="space-y-0">
        @foreach($page->sections->where('is_visible', true)->sortBy('sort_order') as $section)
            @php($content = $section->content ?? [])
            @php($sectionSettings = $section->settings ?? [])
            @php($sectionBackground = data_get($sectionSettings, 'background_color', '#ffffff'))
            @php($sectionText = data_get($sectionSettings, 'text_color', '#27272a'))
            @php($sectionAccent = data_get($sectionSettings, 'accent_color', '#635bff'))
            @php($sectionBorder = data_get($sectionSettings, 'border_color', '#e4e4e7'))
            <section class="border-t px-5 py-14 sm:px-8 sm:py-20" style="background-color: {{ $sectionBackground }}; color: {{ $sectionText }}; border-color: {{ $sectionBorder }}">
                <div class="mx-auto max-w-6xl">
                    @if(filled(data_get($content, 'title')))
                        <h2 class="text-3xl font-black tracking-tight sm:text-4xl">{{ data_get($content, 'title') }}</h2>
                    @endif
                    @if(filled(data_get($content, 'body')))
                        <p class="mt-4 max-w-3xl whitespace-pre-line text-base leading-8" style="color: {{ $sectionText }}">{{ data_get($content, 'body') }}</p>
                    @endif
                    @if(filled(data_get($content, 'image_url')))
                        <img src="{{ data_get($content, 'image_url') }}" alt="{{ data_get($content, 'title', $section->label) }}" loading="lazy" class="mt-6 max-h-[34rem] w-full rounded-3xl object-cover">
                    @endif
                    @if(in_array($section->type, ['features', 'gallery', 'testimonials', 'faq', 'widget'], true) && count(data_get($content, 'items', [])))
                        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach(data_get($content, 'items', []) as $item)
                                <div class="rounded-2xl border p-5" style="background-color: {{ $sectionBackground }}; border-color: {{ $sectionBorder }}; color: {{ $sectionText }}">
                                    <h3 class="font-bold">{{ is_array($item) ? ($item['title'] ?? '') : $item }}</h3>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @if(filled(data_get($content, 'button_label')) && filled(data_get($content, 'button_url')))
                        <a href="{{ data_get($content, 'button_url') }}" class="mt-6 inline-flex rounded-xl px-5 py-3 text-sm font-bold text-white" style="background-color: {{ $sectionAccent }}">{{ data_get($content, 'button_label') }}</a>
                    @endif
                </div>
            </section>
        @endforeach
    </div>
@endif