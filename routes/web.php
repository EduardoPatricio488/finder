<?php

use App\Livewire\AdminAssistant;
use App\Livewire\AdminDashboard;
use App\Livewire\BuilderEditor;
use App\Livewire\CategoryManager;
use App\Livewire\CreateSite;
use App\Livewire\LandingPage;
use App\Livewire\MediaLibrary;
use App\Livewire\MenuManager;
use App\Livewire\OrderManager;
use App\Livewire\PlanSelection;
use App\Livewire\PlatformAdminDashboard;
use App\Livewire\PlatformWebsites;
use App\Livewire\ProductManager;
use App\Livewire\PublicSite;
use App\Livewire\Reports;
use App\Livewire\SiteAnalytics;
use App\Livewire\SiteSettings;
use App\Livewire\StoreSettings;
use App\Livewire\UpgradeSelection;
use App\Livewire\UserDashboard;
use App\Livewire\UserManager;
use App\Models\Site;
use App\Models\SiteAdminAuditLog;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingPage::class)->name('home');
Route::get('planos', UpgradeSelection::class)->name('saas.upgrade');
Route::middleware(['auth', 'verified'])
    ->get('entrada', fn () => redirect()->route('dashboard'))
    ->name('entry');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('dashboard', UserDashboard::class)->name('dashboard');
    Route::get('websites/create', CreateSite::class)->name('site.create');
    Route::get('websites/{site:slug}/builder', BuilderEditor::class)->name('builder.edit');
});

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', PlatformAdminDashboard::class)->name('dashboard');
        Route::get('websites', PlatformWebsites::class)->name('websites');
        Route::get('utilizadores', UserManager::class)->name('users');
        Route::get('configuracoes', StoreSettings::class)->name('config');
        Route::post('websites/{site:slug}/access', function (Site $site) {
            SiteAdminAuditLog::create([
                'site_id' => $site->id,
                'actor_id' => auth()->id(),
                'action' => 'platform_admin_access',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => ['source' => 'platform_admin'],
            ]);
            session(['platform_admin_site_id' => $site->id]);

            return redirect()->route('admin.site.dashboard', $site);
        })->name('websites.access');
        Route::post('websites/exit-access', function () {
            $siteId = session('platform_admin_site_id');
            if ($siteId) {
                SiteAdminAuditLog::create([
                    'site_id' => $siteId,
                    'actor_id' => auth()->id(),
                    'action' => 'platform_admin_exit',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'metadata' => ['source' => 'platform_admin'],
                ]);
            }
            session()->forget('platform_admin_site_id');

            return redirect()->route('admin.websites');
        })->name('websites.exit-access');
    });

Route::middleware(['auth', 'verified', 'site.access'])
    ->prefix('admin/sites/{site:slug}')
    ->name('admin.site.')
    ->group(function (): void {
        Route::get('dashboard', AdminDashboard::class)->name('dashboard');
        Route::get('config', StoreSettings::class)->name('config');
        Route::get('definicoes', SiteSettings::class)->name('settings');
        Route::get('media', MediaLibrary::class)->name('media');
        Route::get('analytics', SiteAnalytics::class)
            ->middleware('can:access-reports,site')
            ->name('analytics');
        Route::get('upgrade', PlanSelection::class)->name('upgrade');
        Route::get('menus', MenuManager::class)->name('menus');
        Route::get('produtos', ProductManager::class)->name('products');
        Route::get('categorias', CategoryManager::class)->name('categories');
        Route::get('encomendas', OrderManager::class)->name('orders');
        Route::get('utilizadores', UserManager::class)->name('users');
        Route::get('relatorios', Reports::class)
            ->middleware('can:access-reports,site')
            ->name('reports');
        Route::get('assistente', AdminAssistant::class)
            ->middleware('can:access-ai,site')
            ->name('assistant');
    });

Route::get('site/{site:slug}/{pageSlug?}', PublicSite::class)->name('site.public');

require __DIR__.'/settings.php';
