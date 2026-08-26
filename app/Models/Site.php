<?php

namespace App\Models;

use Database\Factories\SiteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Route;

/**
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $plan_id
 * @property string $name
 * @property string $slug
 * ... (resto das propriedades)
 */
#[Fillable([
    'name',
    'slug',
    'owner_id',
    'plan_id', // ADICIONADO PARA O SAAS
    'tagline',
    'description',
    'category_label',
    'accent',
    'logo',
    'favicon',
    'primary_color',
    'secondary_color',
    'status',
    'type',
    'subdomain',
    'custom_domain',
    'homepage',
    'social_links',
    'contact_email',
    'phone',
    'address',
    'home_route',
    'is_published',
    'sort_order',
])]
class Site extends Model
{
    /** @use HasFactory<SiteFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'category_label' => 'Site',
        'accent' => 'amber',
        'primary_color' => '#f59e0b',
        'secondary_color' => '#1c1917',
        'status' => 'online',
        'type' => 'online_store',
        'is_published' => true,
        'sort_order' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'homepage' => 'array',
            'social_links' => 'array',
        ];
    }

    // --- RELAÇÕES DO SISTEMA ---

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Relação com o Plano SaaS
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Alias para members() para evitar erros no Seeder e standard Laravel
     */
    public function users(): BelongsToMany
    {
        return $this->members();
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function storeConfig(): HasOne
    {
        return $this->hasOne(StoreConfig::class);
    }

    // --- LÓGICA E SCOPES ---

    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function isProtected(): bool
    {
        return $this->slug === 'casa-co' || $this->home_route === 'sales';
    }

    public function isVisibleTo(?User $user): bool
    {
        return $this->is_published || $user?->isAdministrator() === true;
    }

    public function isManageableBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        if ($user->isAdministrator()) {
            return true;
        }

        return $this->owner_id === $user->id || $this->members()->whereKey($user->id)->exists();
    }

    public function statusLabel(): string
    {
        return $this->status === 'online' ? 'Online' : 'Offline';
    }

    public function hasDedicatedHome(): bool
    {
        return filled($this->home_route) && Route::has($this->home_route);
    }

    public function destinationUrl(): string
    {
        if ($this->hasDedicatedHome()) {
            return route($this->home_route);
        }

        return route('sites.store', $this);
    }

    public function adminUrl(): string
    {
        return route('admin.site.dashboard', $this);
    }
}
