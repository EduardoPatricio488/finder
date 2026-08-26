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
 * @property string $name
 * @property string $slug
 * @property string|null $tagline
 * @property string|null $description
 * @property string|null $logo
 * @property string|null $favicon
 * @property string $category_label
 * @property string $accent
 * @property string $primary_color
 * @property string $secondary_color
 * @property string $status
 * @property string $type
 * @property string|null $subdomain
 * @property string|null $custom_domain
 * @property array<string, mixed>|null $homepage
 * @property array<string, mixed>|null $social_links
 * @property string|null $contact_email
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $home_route
 * @property bool $is_published
 * @property int $sort_order
 */
#[Fillable([
    'name',
    'slug',
    'owner_id',
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

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
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
