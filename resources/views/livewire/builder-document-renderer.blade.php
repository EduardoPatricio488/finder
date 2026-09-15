@php
    $renderNodes = static function (array $nodes) use (&$renderNodes): void {
        foreach ($nodes as $node) {
            if (! is_array($node) || empty($node['type'])) {
                continue;
            }

            $type = $node['type'];
            $content = is_array($node['content'] ?? null) ? $node['content'] : [];
            $settings = is_array($node['settings'] ?? null) ? $node['settings'] : [];
            $children = is_array($node['children'] ?? null) ? $node['children'] : [];
            $align = in_array($settings['align'] ?? 'left', ['left', 'center', 'right', 'justify'], true) ? $settings['align'] : 'left';
            $alignClass = match ($align) { 'center' => 'text-center', 'right' => 'text-right', 'justify' => 'text-justify', default => 'text-left' };
            $spacing = match ($settings['padding'] ?? 'md') { 'none' => 'p-0', 'sm' => 'p-3', 'lg' => 'p-10', 'xl' => 'p-16', default => 'p-6' };

            if ($type === 'container') {
                echo '<div class="mx-auto w-full max-w-7xl '.$spacing.' '.$alignClass.'">';
                $renderNodes($children);
                echo '</div>';
                continue;
            }

            echo '<div class="builder-element '.$alignClass.'">';
            switch ($type) {
                case 'heading':
                    $level = in_array($content['level'] ?? 'h2', ['h1', 'h2', 'h3', 'h4'], true) ? $content['level'] : 'h2';
                    $size = match ($level) { 'h1' => 'text-4xl sm:text-6xl', 'h3' => 'text-2xl', 'h4' => 'text-xl', default => 'text-3xl' };
                    echo '<'.$level.' class="font-bold tracking-tight '.$size.'">'.e((string) ($content['text'] ?? '')).'</'.$level.'>';
                    break;
                case 'text':
                    echo '<p class="whitespace-pre-line leading-8 text-zinc-600 dark:text-zinc-300">'.e((string) ($content['text'] ?? '')).'</p>';
                    break;
                case 'image':
                    $url = (string) ($content['url'] ?? '');
                    if ($url !== '' && preg_match('/^(https?:\/\/|\/|storage\/)/i', $url)) {
                        echo '<figure><img src="'.e($url).'" alt="'.e((string) ($content['alt'] ?? '')).'" loading="lazy" class="mx-auto max-h-[700px] w-full rounded-3xl object-cover shadow-sm">';
                        if (! empty($content['caption'])) echo '<figcaption class="mt-3 text-center text-sm text-zinc-500">'.e((string) $content['caption']).'</figcaption>';
                        echo '</figure>';
                    }
                    break;
                case 'button':
                    $url = (string) ($content['url'] ?? '#');
                    if (! preg_match('/^(https?:\/\/|\/|#)/i', $url)) $url = '#';
                    echo '<a href="'.e($url).'" class="inline-flex rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5" style="background:var(--finder-primary)">'.e((string) ($content['label'] ?? 'Saber mais')).'</a>';
                    break;
                case 'divider':
                    echo '<hr class="my-4 border-zinc-200 dark:border-zinc-700">';
                    break;
                case 'spacer':
                    $height = match ($settings['height'] ?? 'md') { 'sm' => 'h-4', 'lg' => 'h-16', 'xl' => 'h-28', default => 'h-8' };
                    echo '<div class="'.$height.'" aria-hidden="true"></div>';
                    break;
                case 'quote':
                    echo '<blockquote class="border-l-4 border-[var(--finder-primary)] pl-5"><p class="text-xl leading-8">“'.e((string) ($content['text'] ?? '')).'”</p>'.(!empty($content['author']) ? '<footer class="mt-3 text-sm text-zinc-500">'.e((string) $content['author']).'</footer>' : '').'</blockquote>';
                    break;
                case 'video':
                    $url = (string) ($content['url'] ?? '');
                    if ($url !== '' && preg_match('/^https:\/\//i', $url)) echo '<div class="aspect-video overflow-hidden rounded-3xl bg-black"><iframe src="'.e($url).'" title="'.e((string) ($content['title'] ?? 'Vídeo')).'" class="h-full w-full" loading="lazy" allowfullscreen></iframe></div>';
                    break;
                default:
                    if ($children !== []) $renderNodes($children);
                    break;
            }
            echo '</div>';
        }
    };
@endphp

<div class="builder-document mx-auto w-full">
    @php($renderNodes(is_array($document['nodes'] ?? null) ? $document['nodes'] : []))
</div>
