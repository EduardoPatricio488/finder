<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithSiteContext;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class CategoryManager extends Component
{
    use InteractsWithSiteContext;

    public string $name = '';

    public function save(): void
    {
        $site = $this->currentSite();

        $this->name = trim($this->name);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $slug = Str::slug($validated['name']);

        if ($slug === '') {
            $this->addError('name', 'Introduza um nome de categoria válido.');

            return;
        }

        $baseSlug = $slug;
        $suffix = 2;

        while ($site->categories()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        $site->categories()->create([
            'name' => $validated['name'],
            'slug' => $slug,
        ]);

        $this->reset('name');
        $this->resetValidation();
        session()->flash('status', 'Categoria criada com sucesso.');
    }

    public function delete(int $categoryId): void
    {
        $this->currentSite()->categories()->findOrFail($categoryId)->delete();
        session()->flash('status', 'Categoria eliminada com sucesso.');
    }

    public function render(): mixed
    {
        return view('livewire.category-manager', [
            'categories' => $this->currentSite()
                ->categories()
                ->withCount('products')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
