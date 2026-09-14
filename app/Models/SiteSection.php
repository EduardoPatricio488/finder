<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['site_page_id', 'type', 'label', 'content', 'settings', 'sort_order', 'is_visible'])]
class SiteSection extends Model
{
    protected function casts(): array
    {
        return [
            'content' => 'array',
            'settings' => 'array',
            'is_visible' => 'boolean',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(SitePage::class, 'site_page_id');
    }
}
