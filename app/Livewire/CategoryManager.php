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

    public string $categoryName = '';

    public function save(): void
    {
        $site = $this->currentSite();

        $this->categoryName = trim($this->categoryName);

        $validated = $this->validate(
            [
                'categoryName' => ['required', 'string', 'max:255'],
            ],
            [
                'categoryName.required' => 'O nome da categoria é obrigatório.',
                'categoryName.string' => 'O nome da categoria tem de ser um texto válido.',
                'categoryName.max' => 'O nome da categoria não pode ter mais de 255 caracteres.',
            ]
        );

        $slug = Str::slug($validated['categoryName']);

        if ($slug === '') {
            $this->addError('categoryName', 'Introduza um nome de categoria válido.');

            return;
        }

        $baseSlug = $slug;
        $suffix = 2;

        while ($site->categories()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        $site->categories()->create([
            'name' => $validated['categoryName'],
            'slug' => $slug,
        ]);

        $this->reset('categoryName');
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
