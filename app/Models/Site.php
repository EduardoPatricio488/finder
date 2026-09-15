<?php

namespace App\Models;

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

#[Fillable([
    'name', 'slug', 'owner_id', 'plan_id', 'tagline', 'description', 'category_label', 'accent',
    'logo', 'favicon', 'primary_color', 'secondary_color', 'status', 'type', 'subdomain',
    'custom_domain', 'homepage', 'social_links', 'theme', 'settings', 'seo', 'contact_email',
    'phone', 'address', 'home_route', 'is_published', 'published_at', 'sort_order',
])]
class Site extends Model
{
    use HasFactory;

    protected $attributes = [
        'category_label' => 'Site', 'accent' => 'indigo', 'primary_color' => '#635bff',
        'secondary_color' => '#111827', 'status' => 'draft', 'type' => 'business',
        'is_published' => false, 'sort_order' => 0,
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean', 'homepage' => 'array', 'social_links' => 'array',
            'theme' => 'array', 'settings' => 'array', 'seo' => 'array', 'published_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'owner_id'); }
    public function plan(): BelongsTo { return $this->belongsTo(Plan::class); }
    public function users(): BelongsToMany { return $this->members(); }
    public function members(): BelongsToMany { return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps(); }
    public function categories(): HasMany { return $this->hasMany(Category::class); }
    public function products(): HasMany { return $this->hasMany(Product::class); }
    public function customers(): HasMany { return $this->hasMany(Customer::class); }
    public function orders(): HasMany { return $this->hasMany(Order::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function promotions(): HasMany { return $this->hasMany(Promotion::class); }
    public function stockMovements(): HasMany { return $this->hasMany(StockMovement::class); }
    public function storeConfig(): HasOne { return $this->hasOne(StoreConfig::class); }
    public function pages(): HasMany { return $this->hasMany(SitePage::class)->orderBy('sort_order'); }
    public function menus(): HasMany { return $this->hasMany(SiteMenu::class); }
    public function media(): HasMany { return $this->hasMany(SiteMedia::class); }
    public function analyticsEvents(): HasMany { return $this->hasMany(SiteAnalyticsEvent::class); }
    public function adminAuditLogs(): HasMany { return $this->hasMany(SiteAdminAuditLog::class); }
    public function versions(): HasMany { return $this->hasMany(SiteVersion::class)->orderByDesc('version_number'); }

    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query->where('is_published', true)->where('status', 'published');
    }

    public function isProtected(): bool { return $this->slug === 'casa-co' || $this->home_route === 'sales'; }

    public function isVisibleTo(?User $user): bool
    {
        return ($this->is_published && $this->status === 'published') || $user?->isAdministrator() === true;
    }

    public function isManageableBy(?User $user): bool
    {
        if ($user === null) return false;
        if ($user->isAdministrator()) return true;
        return $this->owner_id === $user->id || $this->members()->whereKey($user->id)->exists();
    }

    public function statusLabel(): string
    {
        return match ($this->status) { 'published', 'online' => 'Publicado', 'draft' => 'Rascunho', default => 'Despublicado' };
    }

    public function hasDedicatedHome(): bool { return filled($this->home_route) && Route::has($this->home_route); }
    public function destinationUrl(): string { return $this->hasDedicatedHome() ? route($this->home_route) : route('site.public', $this); }
    public function adminUrl(): string { return route('admin.site.dashboard', $this); }
}
