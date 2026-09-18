@php
    $user = auth()->user();
    $sites = $user
        ? \App\Support\SiteContext::manageableSitesQuery($user)->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug'])
        : collect();
@endphp

<div class="w-full max-w-sm">
    <label for="workspace-site-selector" class="mb-2 block text-[10px] font-black uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Website / Workspace</label>
    <select id="workspace-site-selector" onchange="if (this.value) window.location.href = this.value" class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm font-semibold text-zinc-900 shadow-sm outline-none transition hover:border-indigo-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
        @foreach($sites as $siteOption)
            @php
                $url = match($routeName) {
                    'sales' => route('admin.site.sales', $siteOption),
                    'orders' => route('admin.site.orders', $siteOption),
                    'settings' => route('admin.site.settings', $siteOption),
                    'media' => route('admin.site.media', $siteOption),
                    'menus' => route('admin.site.menus', $siteOption),
                    'users' => route('users'),
                    default => route('admin.site.dashboard', $siteOption),
                };
            @endphp
            <option value="{{ $url }}" @selected((int) $siteOption->id === (int) $currentSite->id)>{{ $siteOption->name }}</option>
        @endforeach
    </select>
    <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">Os dados apresentados pertencem exclusivamente ao website selecionado.</p>
</div>