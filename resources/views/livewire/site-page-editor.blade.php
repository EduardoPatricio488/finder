<div class="min-h-screen bg-zinc-100 p-3 sm:p-5">
    <div class="mx-auto max-w-[1800px]">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-zinc-200 bg-white px-4 py-3 shadow-sm">
            <a href="{{ route('site.manage.menus') }}" class="text-sm font-semibold text-zinc-600 hover:text-zinc-950">← Voltar às páginas</a>
            <div class="min-w-0 text-center">
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Editor visual</p>
                <h1 class="truncate text-lg font-black text-zinc-950">{{ $page->name }}</h1>
            </div>
            <a href="{{ route('site.public', [$site, 'pageSlug' => $page->slug, 'preview' => 1, 'visual_edit' => 1]) }}" target="_blank" class="rounded-xl bg-zinc-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-zinc-700">Abrir em novo separador ↗</a>
        </div>
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xl">
            <iframe
                title="Pré-visualização real com edição direta"
                src="{{ route('site.public', [$site, 'pageSlug' => $page->slug, 'preview' => 1, 'visual_edit' => 1]) }}"
                class="block h-[calc(100vh-125px)] min-h-[650px] w-full bg-white"
                loading="eager"
            ></iframe>
        </div>
    </div>
</div>
