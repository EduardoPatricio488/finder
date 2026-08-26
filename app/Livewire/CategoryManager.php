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
        $validated = $this->validate(['name' => ['required', 'string', 'max:255']]);

        $site->categories()->create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        $this->reset('name');
        session()->flash('status', 'Categoria criada com sucesso.');
    }

    public function delete(int $categoryId): void
    {
        $this->currentSite()->categories()->findOrFail($categoryId)->delete();
        session()->flash('status', 'Categoria excluída com sucesso.');
    }

    public function render(): mixed
    {
        return view('livewire.category-manager', ['categories' => $this->currentSite()->categories()->withCount('products')->orderBy('name')->get()]);
    }
}
