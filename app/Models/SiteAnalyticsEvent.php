<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['site_id', 'page_id', 'event_type', 'path', 'referrer', 'device_type', 'country_code', 'session_hash', 'occurred_at'])]
class SiteAnalyticsEvent extends Model
{
    protected function casts(): array
    {
        return ['occurred_at' => 'datetime'];
    }

    public function site(): BelongsTo { return $this->belongsTo(Site::class); }
    public function page(): BelongsTo { return $this->belongsTo(SitePage::class, 'page_id'); }
}
