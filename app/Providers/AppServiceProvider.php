<?php

namespace App\Providers;

use App\Livewire\Hooks\BuilderStudioSnapshotHook;
use App\Models\Site;
use App\Policies\SitePolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;

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

        Livewire::componentHook(BuilderStudioSnapshotHook::class);

        // Explicitly register the Site policy so site-management authorization
        // is deterministic in every environment, including cached production/local routes.
        Gate::policy(Site::class, SitePolicy::class);

        // --- LÓGICA DE ACESSO SAAS (GATES) ---

        /**
         * Gate: manage
         * Mantém a autorização usada pelo middleware de acesso ao website.
         * A implementação fica centralizada na SitePolicy.
         */
        Gate::define('manage', fn ($user, Site $site): bool => $site->isManageableBy($user));

        /**
         * Gate: access-reports
         * Verifica se o site atual tem permissão para ver relatórios avançados.
         */
        Gate::define('access-reports', function ($user, Site $site) {
            $site->loadMissing('plan');

            return $site->plan && $site->plan->has_reports === true;
        });

        /**
         * Gate: access-ai
         * Verifica se o site atual tem acesso ao Assistente de IA.
         */
        Gate::define('access-ai', function ($user, Site $site) {
            $site->loadMissing('plan');

            return $site->plan && $site->plan->has_ai === true;
        });

        /**
         * Gate: manage-site
         * Compatibilidade com código existente que ainda use este nome.
         */
        Gate::define('manage-site', function ($user, Site $site): bool {
            return $site->isManageableBy($user);
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
