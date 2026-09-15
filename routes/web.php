<?php

use App\Livewire\AdminAssistant;
use App\Livewire\AdminDashboard;
use App\Livewire\BuilderEditor;
use App\Livewire\BuilderStudio;
use App\Livewire\CategoryManager;
use App\Livewire\CreateSite;
use App\Livewire\CustomerAccount;
use App\Livewire\CustomerAssistant;
use App\Livewire\Help;
use App\Livewire\LandingPage;
use App\Livewire\MediaLibrary;
use App\Livewire\MenuManager;
use App\Livewire\OrderManager;
use App\Livewire\OrderTracking;
use App\Livewire\PaymentManager;
use App\Livewire\PlanSelection;
use App\Livewire\PlatformAdminDashboard;
use App\Livewire\PlatformWebsites;
use App\Livewire\ProductCatalog;
use App\Livewire\ProductDetail;
use App\Livewire\ProductManager;
use App\Livewire\PromotionManager;
use App\Livewire\PublicSite;
use App\Livewire\Reports;
use App\Livewire\SalesManager;
use App\Livewire\SiteAnalytics;
use App\Livewire\SiteDirectory;
use App\Livewire\SiteSettings;
use App\Livewire\SiteSubmissions;
use App\Livewire\StockMovementManager;
use App\Livewire\StoreSettings;
use App\Livewire\UpgradeSelection;
use App\Livewire\UserDashboard;
use App\Livewire\UserManager;
use App\Models\Site;
use App\Models\SiteAdminAuditLog;
use Illuminate\Support\Facades\Route;

Route::get('/', SiteDirectory::class)->name('home');
Route::get('finder', LandingPage::class)->name('landing');
Route::get('planos', UpgradeSelection::class)->name('saas.upgrade');
Route::get('vendas', ProductCatalog::class)->name('sales');
Route::get('produtos', ProductCatalog::class)->name('products');
Route::get('produtos/{product:slug}', ProductDetail::class)->name('products.show');
Route::get('sites/{site:slug}', PublicSite::class)->name('sites.show');

Route::middleware(['auth', 'verified'])
    ->get('entrada', fn () => redirect()->route('sales'))
    ->name('entry');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('dashboard', UserDashboard::class)->name('dashboard');
    Route::get('ajuda', Help::class)->name('help');
    Route::get('websites/create', CreateSite::class)->name('site.create');
    Route::get('websites/{site:slug}/builder', BuilderStudio::class)->name('builder.edit');
    Route::get('websites/{site:slug}/builder-classic', BuilderEditor::class)->name('builder.classic');
    Route::get('conta/favoritos', CustomerAccount::class)->defaults('section', 'favorites')->name('account.favorites');
    Route::get('conta/moradas', CustomerAccount::class)->defaults('section', 'addresses')->name('account.addresses');
    Route::get('conta/pagamentos', CustomerAccount::class)->defaults('section', 'payments')->name('account.payments');
    Route::get('conta/notificacoes', CustomerAccount::class)->defaults('section', 'notifications')->name('account.notifications');
    Route::get('conta/{section?}', CustomerAccount::class)->name('account');
    Route::get('encomendas/acompanhamento', OrderTracking::class)->name('orders.tracking');
    Route::get('assistente', CustomerAssistant::class)->name('customer.assistant');
});

Route::get('site/{site:slug}/{pageSlug?}', PublicSite::class)->name('site.public');

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', PlatformAdminDashboard::class)->name('dashboard');
        Route::get('websites', PlatformWebsites::class)->name('websites');
        Route::get('utilizadores', UserManager::class)->name('users');
        Route::get('configuracoes', StoreSettings::class)->name('config');

        $siteRedirect = function (string $route): mixed {
            $site = Site::query()->where('owner_id', auth()->id())->first() ?? Site::query()->first();
            abort_if($site === null, 404, 'Não existe nenhum website disponível para administração.');
            return redirect()->route($route, ['site' => $site]);
        };

        Route::get('products', fn () => $siteRedirect('admin.site.products'))->name('products');
        Route::get('promotions', fn () => $siteRedirect('admin.site.promotions'))->name('promotions');
        Route::get('categories', fn () => $siteRedirect('admin.site.categories'))->name('categories');
        Route::get('sales', fn () => $siteRedirect('admin.site.sales'))->name('sales');
        Route::get('orders', fn () => $siteRedirect('admin.site.orders'))->name('orders');
        Route::get('payments', fn () => $siteRedirect('admin.site.payments'))->name('payments');
        Route::get('customers', fn () => $siteRedirect('admin.site.dashboard'))->name('customers');
        Route::get('reports', fn () => $siteRedirect('admin.site.reports'))->name('reports');
        Route::get('stock', fn () => $siteRedirect('admin.site.stock'))->name('stock');

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
        Route::get('analytics', SiteAnalytics::class)->middleware('can:access-reports,site')->name('analytics');
        Route::get('upgrade', PlanSelection::class)->name('upgrade');
        Route::get('menus', MenuManager::class)->name('menus');
        Route::get('produtos', ProductManager::class)->name('products');
        Route::get('categorias', CategoryManager::class)->name('categories');
        Route::get('encomendas', OrderManager::class)->name('orders');
        Route::get('vendas', SalesManager::class)->name('sales');
        Route::get('pagamentos', PaymentManager::class)->name('payments');
        Route::get('promocoes', PromotionManager::class)->name('promotions');
        Route::get('stock', StockMovementManager::class)->name('stock');
        Route::get('submissoes', SiteSubmissions::class)->name('submissions');
        Route::get('utilizadores', UserManager::class)->name('users');
        Route::get('relatorios', Reports::class)->middleware('can:access-reports,site')->name('reports');
        Route::get('assistente', AdminAssistant::class)->middleware('can:access-ai,site')->name('assistant');
    });

require __DIR__.'/settings.php';
