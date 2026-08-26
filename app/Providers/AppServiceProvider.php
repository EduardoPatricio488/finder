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

        // --- LÓGICA DE ACESSO SAAS (GATES) ---

        /**
         * Gate: access-reports
         * Verifica se o site atual tem permissão para ver relatórios avançados.
         */
        Gate::define('access-reports', function ($user, Site $site) {
            // Carrega o plano se ele não estiver presente
            $site->loadMissing('plan');

            // Permite se o site tiver um plano e a funcionalidade 'has_reports' for verdadeira
            return $site->plan && $site->plan->has_reports === true;
        });

        /**
         * Gate: access-ai
         * Verifica se o site atual tem acesso ao Assistente de IA.
         */
        Gate::define('access-ai', function ($user, Site $site) {
            $site->loadMissing('plan');

            // Permite se o site tiver um plano e a funcionalidade 'has_ai' for verdadeira
            return $site->plan && $site->plan->has_ai === true;
        });

        /**
         * Gate: manage-site
         * Verifica se o utilizador é dono do site ou membro da equipa.
         */
        Gate::define('manage-site', function ($user, Site $site) {
            return $user->id === $site->owner_id || $site->members()->where('user_id', $user->id)->exists();
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
