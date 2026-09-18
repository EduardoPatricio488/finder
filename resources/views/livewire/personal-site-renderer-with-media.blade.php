@include('livewire.personal-site-renderer')

@if($mediaByPlacement->isNotEmpty())
    <section class="border-t border-black/5 bg-white px-5 py-16 sm:px-8 sm:py-20">
        <div class="mx-auto max-w-6xl">
            <p class="text-xs font-bold uppercase tracking-[0.18em]" style="color: var(--finder-primary)">Imagens do website</p>
            <h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Conteúdo visual aplicado</h2>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-zinc-500">As imagens que adicionaste na Biblioteca de Media ficam associadas à área escolhida e são apresentadas automaticamente no website.</p>
            <div class="mt-10 space-y-10">
                @foreach($mediaByPlacement as $placement => $items)
                    <div>
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <h3 class="text-lg font-semibold">{{ match($placement) {
                                'logo' => 'Logótipo', 'hero' => 'Capa / Hero principal', 'about' => 'Sobre mim / Sobre nós',
                                'experience' => 'Experiência', 'education' => 'Formação', 'skills' => 'Competências',
                                'projects' => 'Projetos', 'services' => 'Serviços', 'testimonials' => 'Testemunhos',
                                'gallery' => 'Galeria', 'contact' => 'Contacto', 'background' => 'Fundo de uma secção',
                                'footer' => 'Rodapé', default => 'Media'
                            } }}</h3>
                            <span class="text-xs font-semibold text-zinc-400">{{ $items->count() }} {{ $items->count() === 1 ? 'imagem' : 'imagens' }}</span>
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($items as $item)
                                <figure class="overflow-hidden rounded-3xl border border-black/5 bg-[#f8f8f6] shadow-sm">
                                    <img src="{{ route('site.manage.media.file', ['media' => $item->id]) }}" alt="{{ $item->alt_text ?: $item->original_name }}" class="aspect-[4/3] w-full object-cover" loading="lazy" decoding="async">
                                    <figcaption class="px-4 py-3 text-xs font-semibold text-zinc-500">{{ $item->alt_text ?: $item->original_name }}</figcaption>
                                </figure>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
