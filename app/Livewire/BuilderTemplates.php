<?php

namespace App\Livewire;

use App\Models\Site;
use Illuminate\Support\Str;
use Livewire\Component;

class BuilderTemplates extends Component
{
    public ?Site $site = null;
    public bool $open = false;

    public function mount(): void
    {
        $site = request()->route('site');

        if (! $site instanceof Site || ! $site->isManageableBy(auth()->user())) {
            return;
        }

        $this->site = $site;
    }

    public function apply(string $template): void
    {
        abort_unless($this->site instanceof Site, 403);
        abort_unless($this->site->isManageableBy(auth()->user()), 403);

        $templates = [
            'modern' => [
                'label' => 'Moderno',
                'navigation' => 'top',
                'header_style' => 'clean',
                'hero_layout' => 'center',
                'heading_style' => 'bold',
                'section_style' => 'soft',
                'container' => 'wide',
                'radius' => 'lg',
                'shadow' => 'sm',
            ],
            'sidebar-left' => [
                'label' => 'Sidebar esquerda',
                'navigation' => 'left',
                'header_style' => 'sidebar',
                'hero_layout' => 'left',
                'heading_style' => 'bold',
                'section_style' => 'cards',
                'container' => 'wide',
                'radius' => 'lg',
                'shadow' => 'md',
            ],
            'sidebar-right' => [
                'label' => 'Sidebar direita',
                'navigation' => 'right',
                'header_style' => 'sidebar',
                'hero_layout' => 'right',
                'heading_style' => 'elegant',
                'section_style' => 'cards',
                'container' => 'wide',
                'radius' => 'lg',
                'shadow' => 'md',
            ],
            'minimal' => [
                'label' => 'Minimalista',
                'navigation' => 'top',
                'header_style' => 'minimal',
                'hero_layout' => 'center',
                'heading_style' => 'minimal',
                'section_style' => 'flat',
                'container' => 'narrow',
                'radius' => 'md',
                'shadow' => 'none',
            ],
            'business' => [
                'label' => 'Empresa',
                'navigation' => 'top',
                'header_style' => 'corporate',
                'hero_layout' => 'left',
                'heading_style' => 'corporate',
                'section_style' => 'cards',
                'container' => 'wide',
                'radius' => 'md',
                'shadow' => 'md',
            ],
            'portfolio' => [
                'label' => 'Portfólio',
                'navigation' => 'top',
                'header_style' => 'minimal',
                'hero_layout' => 'left',
                'heading_style' => 'editorial',
                'section_style' => 'flat',
                'container' => 'wide',
                'radius' => 'sm',
                'shadow' => 'none',
            ],
            'store' => [
                'label' => 'Loja',
                'navigation' => 'top',
                'header_style' => 'store',
                'hero_layout' => 'center',
                'heading_style' => 'bold',
                'section_style' => 'cards',
                'container' => 'wide',
                'radius' => 'lg',
                'shadow' => 'md',
            ],
        ];

        abort_unless(isset($templates[$template]), 422);
        $definition = $templates[$template];

        $theme = $this->site->theme ?: [];
        foreach ($definition as $key => $value) {
            if ($key !== 'label') {
                $theme[$key] = $value;
            }
        }
        $theme['template'] = $template;
        $theme['template_label'] = $definition['label'];
        $theme['heading_font'] = $theme['heading_font'] ?? 'Inter';
        $theme['body_font'] = $theme['body_font'] ?? 'Inter';
        $theme['content_width'] = $definition['container'] === 'narrow' ? '1040px' : '1200px';

        $this->site->update(['theme' => $theme]);

        $pages = $this->site->pages()->with('sections')->get();
        foreach ($pages as $page) {
            foreach ($page->sections as $section) {
                $settings = $section->settings ?? [];
                $settings['layout'] = $definition['section_style'];
                $settings['align'] = $definition['hero_layout'];
                $settings['radius'] = $definition['radius'];
                $settings['shadow'] = $definition['shadow'];
                $section->update(['settings' => $settings]);
            }
        }

        $this->open = false;
        $this->dispatch('builder-template-applied', template: $template);
    }

    public function render(): mixed
    {
        return view('livewire.builder-templates');
    }
}
