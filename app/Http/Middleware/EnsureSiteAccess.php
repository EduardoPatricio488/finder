<?php

namespace App\Http\Middleware;

use App\Models\Site;
use App\Support\SiteContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class EnsureSiteAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();
        $site = $route?->parameter('site');

        if (! $site instanceof Site && filled($site)) {
            $site = Site::query()->where('slug', (string) $site)->first();
            abort_unless($site instanceof Site, 404);
            $route->setParameter('site', $site);
        }

        if (! $site instanceof Site) {
            $site = SiteContext::current();
        }

        abort_unless($site instanceof Site, 404);
        Gate::authorize('manage', $site);
        $request->session()->put('current_site_id', $site->id);

        return $next($request);
    }
