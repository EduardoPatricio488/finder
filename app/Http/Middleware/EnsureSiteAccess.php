<?php

namespace App\Http\Middleware;

use App\Models\Site;
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

        // Route model binding should normally resolve {site:slug} to a Site model.
        // Resolve it defensively here as well so this access middleware never
        // returns a false 404 merely because binding has not run yet.
        if (! $site instanceof Site && filled($site)) {
            $site = Site::query()
                ->where('slug', (string) $site)
                ->first();

            abort_unless($site instanceof Site, 404);

            $route->setParameter('site', $site);
        }

        abort_unless($site instanceof Site, 404);

        Gate::authorize('manage', $site);

        $request->session()->put('current_site_id', $site->id);

        return $next($request);
    }
}
