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

    protected $rules = [
        "name" => "required|min:3|max:50",
        "slug" => "required|unique:sites,slug|alpha_dash",
    ];

    public function updatedName($value) { $this->slug = Str::slug($value); }

    public function create()
    {
        $this->validate();
        $plan = Plan::where("name", "Free")->first() ?? Plan::first();
        $site = Site::create([
            "name" => $this->name,
            "slug" => $this->slug,
            "owner_id" => auth()->id(),
            "plan_id" => $plan?->id,
            "status" => "online",
            "is_published" => true,
        ]);
        $site->members()->attach(auth()->id(), ["role" => "admin"]);
        return redirect()->route("admin.site.dashboard", $site->slug);
    }

    public function render() { return view("livewire.create-site"); }
}