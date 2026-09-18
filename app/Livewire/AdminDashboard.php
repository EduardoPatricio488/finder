<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithSiteContext;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class AdminDashboard extends Component
{
    use InteractsWithSiteContext;

    public array $modelContent = [];

    public function mount(): void
    {
        $site = $this->currentSite();
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

        $settings = $this->currentSite()->settings ?? [];
        $settings['model_content'] = collect($this->modelContent)
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->all();

        $this->currentSite()->update(['settings' => $settings]);

        session()->flash('model-content-saved', 'Conteúdo do modelo guardado com sucesso.');
    }

    private function modelProfile(): array
    {
        $type = (string) $this->currentSite()->type;

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
        $site = $this->currentSite();
        $profile = $this->modelProfile();
        $fields = $profile['fields'] ?? [];
        $filled = collect($fields)->filter(function (array $field): bool {
            $value = $this->modelContent[$field['key']] ?? '';

            return filled($value);
        })->count();

        $memberCount = $site->members()->count();
        $ownerIsMember = $site->owner_id !== null
            && $site->members()->whereKey($site->owner_id)->exists();

        return view('livewire.admin-dashboard', [
            'site' => $site,
            'activeProducts' => $site->products()->where('is_active', true)->count(),
            'totalProducts' => $site->products()->count(),
            'totalUsers' => $memberCount + (($site->owner_id !== null && ! $ownerIsMember) ? 1 : 0),
            'recentUsers' => $site->members()->latest()->limit(5)->get(),
            'revenue' => (float) $site->orders()->whereDate('sold_at', today())->where('status', '!=', 'cancelada')->sum('total'),
            'sales' => $site->orders()->whereDate('sold_at', today())->where('status', '!=', 'cancelada')->count(),
            'lowStockProducts' => $site->products()->whereColumn('stock', '<=', 'minimum_stock')->orderBy('stock')->limit(5)->get(),
            'modelProfile' => $profile,
            'modelFields' => $fields,
            'modelFilled' => $filled,
            'modelTotal' => count($fields),
        ]);
    }
}
