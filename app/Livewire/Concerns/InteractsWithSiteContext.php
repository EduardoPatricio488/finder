<?php

namespace App\Livewire\Concerns;

use App\Models\Site;
use App\Support\SiteContext;

trait InteractsWithSiteContext
{
    protected function currentSite(): Site
    {
        return SiteContext::current();
    }
}
