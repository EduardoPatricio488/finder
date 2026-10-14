<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse;

class SiteLoginResponse implements LoginResponse
{
    public function toResponse($request): JsonResponse|RedirectResponse
    {
        if ($request instanceof Request && $request->wantsJson()) {
            return new JsonResponse('', 204);
        }

        if ($request instanceof Request) {
            $siteRedirect = $this->safeSiteRedirect($request, $request->input('redirect'));

            if ($siteRedirect !== null) {
                $request->session()->forget('url.intended');

                return redirect()->to($siteRedirect);
            }
        }

        return redirect()->intended(route('dashboard'));
    }

    private function safeSiteRedirect(Request $request, mixed $candidate): ?string
    {
        if (! is_string($candidate) || trim($candidate) === '') {
            return null;
        }

        $parts = parse_url($candidate);

        if ($parts === false || empty($parts['host']) || empty($parts['path'])) {
            return null;
        }

        if (! hash_equals($request->getHost(), (string) $parts['host'])) {
            return null;
        }

        if (isset($parts['scheme']) && ! in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return null;
        }

        if (! preg_match('#^/site/[a-z0-9-]+(?:/[^/]*)?$#i', $parts['path'])) {
            return null;
        }

        if (preg_match('#/login/?$#i', $parts['path'])) {
            return null;
        }

        return $candidate;
    }
}
