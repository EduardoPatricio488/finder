<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['site_id', 'name', 'slug', 'status', 'is_homepage', 'seo', 'sort_order'])]
class SitePage extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_homepage' => 'boolean',
            'seo' => 'array',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(SiteSection::class)->orderBy('sort_order');
    }
}
