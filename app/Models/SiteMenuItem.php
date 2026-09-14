<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['site_menu_id', 'site_page_id', 'parent_id', 'label', 'url', 'target', 'sort_order', 'is_visible'])]
class SiteMenuItem extends Model
{
    protected function casts(): array
    {
        return ['is_visible' => 'boolean'];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(SiteMenu::class, 'site_menu_id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(SitePage::class, 'site_page_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }
}
