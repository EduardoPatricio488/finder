<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToSite
{
    protected static function bootBelongsToSite()
    {
        static::addGlobalScope('site_id', function (Builder $builder) {
            if (request()->route('site')) {
                $site = request()->route('site');
                $id = is_object($site) ? $site->id : $site;
                $builder->where('site_id', $id);
            }
        });

        static::creating(function ($model) {
            if (request()->route('site')) {
                $site = request()->route('site');
                $model->site_id = is_object($site) ? $site->id : $site;
            }
        });
    }
}
