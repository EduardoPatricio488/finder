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
        $site = $request->route('site');

        abort_unless($site instanceof Site, 404);

        Gate::authorize('manage', $site);

        $request->session()->put('current_site_id', $site->id);

        return $next($request);
    }
}
