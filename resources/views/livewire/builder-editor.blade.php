<div class="h-screen w-screen overflow-hidden bg-white">
    <iframe
        src="{{ route('site.public', $site) }}?preview=1"
        title="Pré-visualização do website {{ $site->name }}"
        class="block h-full w-full border-0"
        loading="eager"
    ></iframe>
</div>
