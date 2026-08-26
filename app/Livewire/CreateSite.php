<?php

namespace App\Livewire;

use App\Models\Site;
use App\Models\Plan;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Title("Criar Minha Loja")]
#[Layout("layouts.app")]
class CreateSite extends Component
{
    public string $name = "";
    public string $slug = "";

    /**
     * Regras de validação
     */
    protected $rules = [
        "name" => "required|min:3|max:50",
        "slug" => "required|unique:sites,slug|alpha_dash",
    ];

    /**
     * Gera o link (slug) automaticamente enquanto escreve o nome
     */
    public function updatedName($value)
    {
        $this->slug = Str::slug($value);
    }

    /**
     * Cria a loja no banco de dados e vincula o utilizador
     */
    public function create()
    {
        $this->validate();

        // Procura o plano inicial (Free)
        $plan = Plan::where("name", "Free")->first() ?? Plan::first();

        // Cria o registo do Site
        $site = Site::create([
            "name" => $this->name,
            "slug" => $this->slug,
            "owner_id" => auth()->id(),
            "plan_id" => $plan?->id,
            "status" => "online",
            "is_published" => true,
            "category_label" => "Loja Online", // Padrão inicial
            "accent" => "amber",               // Cor padrão inicial
            "type" => "online_store",
            "tagline" => "Bem-vindo à nossa nova loja.",
        ]);

        // Vincula o criador como Administrador do site na tabela pivot
        $site->members()->attach(auth()->id(), ["role" => "admin"]);

        // Mensagem de sucesso para o utilizador
        session()->flash('status', 'A sua loja "' . $this->name . '" foi criada com sucesso! 🚀');

        // Redireciona diretamente para o Dashboard da nova loja
        return redirect()->route("admin.site.dashboard", $site->slug);
    }

    public function render()
    {
        return view("livewire.create-site");
    }
}
