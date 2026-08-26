<?php

namespace App\Policies;

use App\Models\Site;
use App\Models\User;

class SitePolicy
{
    public function view(User $user, Site $site): bool
    {
        return $site->isVisibleTo($user);
    }

    public function manage(User $user, Site $site): bool
    {
        return $site->isManageableBy($user);
    }
}
