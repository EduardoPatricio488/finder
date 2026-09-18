<?php

namespace App\Livewire;

use App\Models\Site;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Configuração do site')]
class SiteSettings extends Component
{
    public Site $site;

    public array $modelContent = [];

    public function mount(Site $site): void
    {
        abort_unless($site->isManageableBy(auth()->user()), 403);

        $this->site = $site;
        $this->modelContent = data_get($site->settings, 'model_content', []);

        foreach ($this->modelProfile()['fields'] as $field) {
            $key = (string) ($field['key'] ?? '');

            if ($key !== '' && ! array_key_exists($key, $this->modelContent)) {
                $this->modelContent[$key] = '';
            }
        }
    }

    public function saveModelContent(): void
    {
        $profile = $this->modelProfile();
        $rules = [];

        foreach ($profile['fields'] as $field) {
            $key = (string) ($field['key'] ?? '');

            if ($key === '') {
                continue;
            }

            $rules["modelContent.{$key}"] = ($field['required'] ?? false)
                ? ['required', 'string', 'max:5000']
                : ['nullable', 'string', 'max:5000'];

            if (($field['type'] ?? 'text') === 'email') {
                $rules["modelContent.{$key}"][] = 'email';
            }

            if (($field['type'] ?? 'text') === 'url') {
                $rules["modelContent.{$key}"][] = 'url';
            }
        }

        $this->validate($rules);

        $settings = $this->site->settings ?? [];
        $settings['model_content'] = collect($this->modelContent)
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->all();

        $this->site->update(['settings' => $settings]);
        $this->site->refresh();

        session()->flash('model-content-saved', 'Configuração do site guardada com sucesso.');
    }

    private function modelProfile(): array
    {
        $type = (string) $this->site->type;

        return config("website.model_profiles.{$type}", [
            'label' => config("website.types.{$type}.label", 'Website'),
            'description' => 'Configura os conteúdos específicos deste website.',
            'icon' => 'globe-alt',
            'fields' => [],
            'sidebar' => [],
        ]);
    }

    public function render(): mixed
    {
        $profile = $this->modelProfile();
        $fields = $profile['fields'] ?? [];

        $filled = collect($fields)->filter(function (array $field): bool {
            return filled($this->modelContent[$field['key']] ?? '');
        })->count();

        return view('livewire.site-settings', [
            'modelProfile' => $profile,
            'modelFields' => $fields,
            'modelFilled' => $filled,
            'modelTotal' => count($fields),
        ]);
    }
}
