<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['site_id', 'actor_id', 'action', 'ip_address', 'user_agent', 'metadata'])]
class SiteAdminAuditLog extends Model
{
    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function site(): BelongsTo { return $this->belongsTo(Site::class); }
    public function actor(): BelongsTo { return $this->belongsTo(User::class, 'actor_id'); }
}
