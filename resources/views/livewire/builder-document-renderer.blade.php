@php
    use App\Support\WebsiteBuilder\ResponsiveStyles;

    $device = in_array($device ?? 'desktop', ['desktop', 'tablet', 'mobile'], true) ? ($device ?? 'desktop') : 'desktop';
    $selectedElementId = $selectedElementId ?? null;
    $renderNodes = static function (array $nodes) use (&$renderNodes, $device, $selectedElementId): void {
        foreach ($nodes as $node) {
            if (! is_array($node) || empty($node['type'])) continue;
            $type = $node['type'];
            $nodeId = (string) ($node['id'] ?? '');
            $content = is_array($node['content'] ?? null) ? $node['content'] : [];
            $settings = is_array($node['settings'] ?? null) ? $node['settings'] : [];
            $children = is_array($node['children'] ?? null) ? $node['children'] : [];
            $styles = ResponsiveStyles::cssForNode($settings, $device);
            $styleAttribute = $styles !== [] ? ' style="'.e(collect($styles)->map(fn (string $value, string $key): string => $key.':'.$value)->implode(';')).'"' : '';
            $resolvedStyles = ResponsiveStyles::forDevice($settings, $device);
            $align = in_array($resolvedStyles['align'] ?? $settings['align'] ?? 'left', ['left','center','right','justify'], true) ? ($resolvedStyles['align'] ?? $settings['align'] ?? 'left') : 'left';
            $alignClass = match ($align) { 'center'=>'text-center','right'=>'text-right','justify'=>'text-justify',default=>'text-left' };
            $spacing = match ($resolvedStyles['padding'] ?? $settings['padding'] ?? 'md') { 'none'=>'p-0','sm'=>'p-3','lg'=>'p-10','xl'=>'p-16',default=>'p-6' };
            if ($type === 'container') { echo '<div class="mx-auto w-full max-w-7xl '.$spacing.' '.$alignClass.'"'.$styleAttribute.'>'; $renderNodes($children); echo '</div>'; continue; }
            $isSelected = $nodeId !== '' && $nodeId === $selectedElementId;
            echo '<div class="builder-element group/el relative '.$alignClass.' rounded-lg transition '.($isSelected ? 'ring-2 ring-blue-400' : 'hover:ring-2 hover:ring-blue-200').'"'.$styleAttribute.'>';
            echo '<button type="button" wire:click.stop="removeElement(\''.e($nodeId).'\')" title="Remover elemento" class="absolute -right-2 -top-2 z-10 hidden h-5 w-5 items-center justify-center rounded-full bg-red-600 text-[10px] font-bold text-white shadow group-hover/el:flex">×</button>';
            switch ($type) {
                case 'heading':
                    $level = in_array($content['level'] ?? 'h2', ['h1','h2','h3','h4'], true) ? $content['level'] : 'h2';
                    $size = match ($level) { 'h1'=>'text-4xl sm:text-6xl','h3'=>'text-2xl','h4'=>'text-xl',default=>'text-3xl' };
                    echo '<'.$level.' wire:click.stop="selectElement(\''.e($nodeId).'\')" contenteditable="true" spellcheck="true" x-on:focus="$wire.selectElement(\''.e($nodeId).'\')" x-on:blur="window.dispatchEvent(new CustomEvent(\'builder-autosave\')); $wire.updateSelectedElement(\'text\', $el.innerText).then(() => $wire.save()).then(() => window.dispatchEvent(new CustomEvent(\'builder-autosave-finished\'))).catch(() => window.dispatchEvent(new CustomEvent(\'builder-autosave-error\')))" x-on:keydown.enter.prevent="$el.blur()" class="font-bold tracking-tight '.$size.' cursor-text outline-none rounded-lg">'.e((string) ($content['text'] ?? '')).'</'.$level.'>';
                    break;
                case 'text':
                    echo '<p wire:click.stop="selectElement(\''.e($nodeId).'\')" contenteditable="true" spellcheck="true" x-on:focus="$wire.selectElement(\''.e($nodeId).'\')" x-on:blur="window.dispatchEvent(new CustomEvent(\'builder-autosave\')); $wire.updateSelectedElement(\'text\', $el.innerText).then(() => $wire.save()).then(() => window.dispatchEvent(new CustomEvent(\'builder-autosave-finished\'))).catch(() => window.dispatchEvent(new CustomEvent(\'builder-autosave-error\')))" class="whitespace-pre-line leading-8 text-zinc-600 dark:text-zinc-300 cursor-text outline-none rounded-lg">'.e((string) ($content['text'] ?? '')).'</p>';
                    break;
                case 'image':
                    $url = (string) ($content['url'] ?? '');
                    if ($url !== '' && preg_match('/^(https?:\/\/|\/|storage\/)/i', $url)) echo '<figure><img src="'.e($url).'" alt="'.e((string) ($content['alt'] ?? '')).'" loading="lazy" class="mx-auto max-h-[700px] w-full rounded-3xl object-cover shadow-sm">'.(!empty($content['caption']) ? '<figcaption class="mt-3 text-center text-sm text-zinc-500">'.e((string)$content['caption']).'</figcaption>' : '').'</figure>';
                    break;
                case 'button':
                    echo '<a href="#" wire:click.stop.prevent="selectElement(\''.e($nodeId).'\')" contenteditable="true" spellcheck="true" x-on:focus="$wire.selectElement(\''.e($nodeId).'\')" x-on:blur="window.dispatchEvent(new CustomEvent(\'builder-autosave\')); $wire.updateSelectedElement(\'label\', $el.innerText).then(() => $wire.save()).then(() => window.dispatchEvent(new CustomEvent(\'builder-autosave-finished\'))).catch(() => window.dispatchEvent(new CustomEvent(\'builder-autosave-error\')))" x-on:keydown.enter.prevent="$el.blur()" class="inline-flex rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 cursor-text outline-none" style="background:var(--finder-primary)">'.e((string) ($content['label'] ?? 'Saber mais')).'</a>';
                    break;
                case 'divider': echo '<hr class="my-4 border-zinc-200 dark:border-zinc-700">'; break;
                case 'spacer':
                    $height = match ($settings['height'] ?? 'md') { 'sm'=>'h-4','lg'=>'h-16','xl'=>'h-28',default=>'h-8' };
                    echo '<div class="'.$height.'" aria-hidden="true"></div>'; break;
                case 'quote':
                    echo '<blockquote class="border-l-4 border-[var(--finder-primary)] pl-5"><p wire:click.stop="selectElement(\''.e($nodeId).'\')" contenteditable="true" spellcheck="true" x-on:focus="$wire.selectElement(\''.e($nodeId).'\')" x-on:blur="window.dispatchEvent(new CustomEvent(\'builder-autosave\')); $wire.updateSelectedElement(\'text\', $el.innerText).then(() => $wire.save()).then(() => window.dispatchEvent(new CustomEvent(\'builder-autosave-finished\'))).catch(() => window.dispatchEvent(new CustomEvent(\'builder-autosave-error\')))" class="text-xl leading-8 cursor-text outline-none rounded-lg">"'.e((string)($content['text'] ?? '')).'"</p>'.(!empty($content['author']) ? '<footer class="mt-3 text-sm text-zinc-500">'.e((string)$content['author']).'</footer>' : '').'</blockquote>';
                    break;
                case 'video':
                    $url = (string)($content['url'] ?? ''); if ($url !== '' && preg_match('/^https:\/\//i',$url)) echo '<div class="aspect-video overflow-hidden rounded-3xl bg-black"><iframe src="'.e($url).'" title="'.e((string)($content['title'] ?? 'Vídeo')).'" class="h-full w-full" loading="lazy" allowfullscreen></iframe></div>'; break;
                case 'gallery':
                    $items = array_values(array_filter($content['items'] ?? [], 'is_array')); echo '<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">'; foreach($items as $item) if(!empty($item['url']) && preg_match('/^(https?:\/\/|\/|storage\/)/i',(string)$item['url'])) echo '<img src="'.e((string)$item['url']).'" alt="'.e((string)($item['alt'] ?? '')).'" loading="lazy" class="aspect-[4/3] w-full rounded-2xl object-cover">'; echo '</div>'; break;
                case 'faq':
                    echo '<div class="mx-auto max-w-3xl"><h2 class="mb-5 text-2xl font-bold">'.e((string)($content['title'] ?? 'Perguntas frequentes')).'</h2><div class="divide-y divide-zinc-200 dark:divide-zinc-800">'; foreach(array_values(array_filter($content['items'] ?? [], 'is_array')) as $item) echo '<details class="py-4"><summary class="cursor-pointer font-semibold">'.e((string)($item['question'] ?? $item['title'] ?? '')).'</summary><p class="mt-2 leading-7 text-zinc-600 dark:text-zinc-300">'.e((string)($item['answer'] ?? $item['description'] ?? '')).'</p></details>'; echo '</div></div>'; break;
                case 'social_links':
                    echo '<div class="flex flex-wrap justify-center gap-3">'; foreach(array_values(array_filter($content['items'] ?? [], 'is_array')) as $item) { $url=(string)($item['url'] ?? '#'); if(!preg_match('/^https:\/\//i',$url))$url='#'; echo '<a href="'.e($url).'" target="_blank" rel="noopener noreferrer" class="rounded-xl border border-zinc-200 px-4 py-2 text-sm font-medium dark:border-zinc-800">'.e((string)($item['label'] ?? $item['name'] ?? 'Rede social')).'</a>'; } echo '</div>'; break;
                case 'form': echo '<div class="mx-auto max-w-2xl rounded-3xl border border-zinc-200 p-7 dark:border-zinc-800"><h2 class="text-2xl font-bold">'.e((string)($content['title'] ?? 'Fala connosco')).'</h2><p class="mt-2 text-sm text-zinc-500">Preenche o formulário de contacto para entrares em contacto.</p><div class="mt-6 space-y-3"><div class="h-11 rounded-xl bg-zinc-100 dark:bg-zinc-900"></div><div class="h-11 rounded-xl bg-zinc-100 dark:bg-zinc-900"></div><div class="h-28 rounded-xl bg-zinc-100 dark:bg-zinc-900"></div></div></div>'; break;
                case 'html': echo '<div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">HTML personalizado disponível apenas através de uma extensão segura.</div>'; break;
                default: if($children !== []) $renderNodes($children); break;
            }
            echo '</div>';
        }
    };
@endphp
<div class="builder-document mx-auto w-full">@php($renderNodes(is_array($document['nodes'] ?? null) ? $document['nodes'] : []))</div>
