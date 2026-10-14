<?php

namespace App\Livewire;

use App\Models\Site;
use App\Services\FinderNotificationService;
use App\Support\SiteContext;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Configuração do site')]
class SiteSettings extends Component
{
    public Site $site;

    public array $modelContent = [];

    public bool $allowSearchIndexing = true;

    public string $siteLanguage = 'pt-PT';

    public string $siteDescription = '';

    public string $analyticsId = '';

    public bool $showFinderBranding = true;

    public bool $modelContentApplied = false;

    public bool $modelContentSaved = false;

    public function mount(): void
    {
        $site = SiteContext::current();
        abort_unless($site instanceof Site && $site->isManageableBy(auth()->user()), 403);

        $this->site = $site;
        $this->allowSearchIndexing = (bool) data_get($site->settings, 'preferences.allow_search_indexing', true);
        $this->siteLanguage = (string) data_get($site->settings, 'preferences.language', 'pt-PT');
        $this->siteDescription = (string) data_get($site->settings, 'preferences.site_description', '');
        $this->analyticsId = (string) data_get($site->settings, 'preferences.analytics_id', '');
        $this->showFinderBranding = (bool) data_get($site->settings, 'preferences.show_finder_branding', true);
        session()->put('current_site_id', $site->id);
        $this->modelContent = data_get($site->settings, 'model_content', []);
        $this->modelContentApplied = filled(data_get($site->settings, 'model_content_applied_at'));
        $this->modelContentSaved = filled(data_get($site->settings, 'model_content'));

        $brief = data_get($site->settings, 'builder.brief', []);

        // Os dados introduzidos na criação do website são reutilizados
        // automaticamente na configuração específica do modelo.
        if ($this->site->type === 'personal') {
            if (! filled($this->modelContent['display_name'] ?? null) && filled($brief['business_name'] ?? null)) {
                $this->modelContent['display_name'] = trim((string) $brief['business_name']);
            }

            if (! filled($this->modelContent['bio'] ?? null) && filled($brief['description'] ?? null)) {
                $this->modelContent['bio'] = trim((string) $brief['description']);
            }
        }

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
        app(FinderNotificationService::class)->siteChanged($this->site, 'A configuração do website foi alterada.');
        $this->site->refresh();

        $this->modelContentSaved = true;
        $this->modelContentApplied = false;
        session()->flash('model-content-saved', 'Configuração do site guardada. Agora podes aplicar os dados no site.');
    }

    public function savePreferences(): void
    {
        $this->validate([
            'siteLanguage' => ['required', 'in:pt-PT,en-GB,es-ES,fr-FR'],
            'siteDescription' => ['nullable', 'string', 'max:320'],
            'analyticsId' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9._:-]*$/'],
        ]);

        $settings = $this->site->settings ?? [];
        $settings['preferences'] = array_merge($settings['preferences'] ?? [], [
            'allow_search_indexing' => $this->allowSearchIndexing,
            'language' => $this->siteLanguage,
            'site_description' => trim($this->siteDescription),
            'analytics_id' => trim($this->analyticsId),
            'show_finder_branding' => $this->showFinderBranding,
        ]);

        $this->site->update(['settings' => $settings]);
        app(FinderNotificationService::class)->siteChanged($this->site, 'As preferências gerais do website foram alteradas.');
        $this->site->refresh();

        session()->flash('preferences-saved', 'Preferências gerais guardadas com sucesso.');
    }

    public function updatedModelContent(): void
    {
        $this->modelContentSaved = false;
        $this->modelContentApplied = false;
    }

    public function applyModelContent(): void
    {
        abort_unless($this->modelContentSaved, 422, 'Guarda primeiro a configuração antes de a aplicar no site.');

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
        $settings['model_content_applied_at'] = now()->toIso8601String();

        $this->site->update(['settings' => $settings]);
        $this->site->refresh();
        $this->modelContentApplied = true;

        session()->flash('model-content-applied', 'Os dados foram aplicados no site com sucesso.');
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
            'modelContentApplied' => $this->modelContentApplied,
            'modelContentSaved' => $this->modelContentSaved,
        ]);
    }
}
