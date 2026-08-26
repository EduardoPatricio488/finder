<?php

namespace App\Providers;

use App\Models\Site;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        // --- LÓGICA DE PLANOS SAAS ---

        // Define quem pode ver os Relatórios
        Gate::define('access-reports', function ($user, Site $site) {
            // Verifica se o site tem um plano e se esse plano permite relatórios
            return $site->plan && $site->plan->has_reports;
        });

        // Define quem pode usar o Assistente IA
        Gate::define('access-ai', function ($user, Site $site) {
            // Verifica se o site tem um plano e se esse plano permite IA
            return $site->plan && $site->plan->has_ai;
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
