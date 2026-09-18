<?php

namespace App\Support;

use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SiteContext
{
    public static function current(?Request $request = null): Site
    {
        $request ??= request();
        $routeSite = $request->route('site');

        if ($routeSite instanceof Site) {
            abort_unless($routeSite->isManageableBy($request->user()), 404);

            return $routeSite;
        }

        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $site = self::manageableSitesQuery($user)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->first();

        abort_unless($site instanceof Site, 404);

        if ($request->hasSession()) {
            $request->session()->put('current_site_id', $site->id);
        }

        return $site;
    }

    public static function adminRoute(string $name, ?Site $site = null): string
    {
        return route('admin.site.'.$name, $site ?? self::current());
    }

    public static function manageableSitesQuery(User $user): Builder
    {
        $query = Site::query();

        if (! Schema::hasColumn('sites', 'owner_id') || ! Schema::hasTable('site_user')) {
            return $user->isAdministrator() ? $query : $query->published();
        }

        return $query->where(function ($query) use ($user): void {
            $query->where('owner_id', $user->id)
                ->orWhereHas('members', fn ($members) => $members->whereKey($user->id));

            if ($user->isAdministrator()) {
                $query->orWhereNotNull('id');
            }
        });
    }

    public static function storefront(?Request $request = null): Site
    {
        $request ??= request();
        $routeSite = $request->route('site');

        if ($routeSite instanceof Site) {
            abort_unless($routeSite->status === 'online' && $routeSite->is_published, 404);

            return $routeSite;
        }

        return Site::query()->where('slug', 'casa-co')->firstOrFail();
    }
}
