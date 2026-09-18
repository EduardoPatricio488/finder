<?php

use App\Livewire\AdminDashboard;
use App\Livewire\CategoryManager;
use App\Livewire\CreateSite;
use App\Livewire\CustomerAccount;
use App\Livewire\Help;
use App\Livewire\LandingPage;
use App\Livewire\MediaLibrary;
use App\Livewire\MenuManager;
use App\Livewire\Notifications;
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
use App\Livewire\SalesManager;
use App\Livewire\SiteDefinitions;
use App\Livewire\SiteSettings;
use App\Livewire\SiteSubmissions;
use App\Livewire\StockMovementManager;
use App\Livewire\StoreSettings;
use App\Livewire\UpgradeSelection;
use App\Livewire\UserDashboard;
use App\Livewire\UserManager;
use App\Models\Site;
use App\Models\SiteAdminAuditLog;
use App\Models\SiteMedia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingPage::class)->name('home');
Route::get('planos', UpgradeSelection::class)->name('saas.upgrade');
Route::get('produtos', ProductCatalog::class)->name('products');
Route::middleware(['auth', 'verified', 'site.access'])->get('vendas', SalesManager::class)->name('sales');
Route::middleware(['auth', 'verified', 'site.access'])->get('encomendas', OrderManager::class)->name('orders');
Route::middleware(['auth', 'verified', 'site.access'])->get('utilizadores', UserManager::class)->name('users');
Route::get('site/{site}/produtos/{product:slug}', ProductDetail::class)->name('site.products.show');
Route::get('produtos/{product:slug}', ProductDetail::class)->name('products.show');

Route::middleware(['auth', 'verified'])
    ->get('entrada', fn () => redirect()->route('dashboard'))
    ->name('entry');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('dashboard', UserDashboard::class)->name('dashboard');
    Route::get('ajuda', Help::class)->name('help');
    Route::get('notificacoes', Notifications::class)->name('notifications');
    Route::get('websites/create', CreateSite::class)->name('site.create');
    Route::get('builder', function () {
        $site = \App\Support\SiteContext::current();
        abort_unless($site instanceof Site, 404);
        return redirect()->route('site.public', ['site' => $site, 'preview' => 1]);
    })->name('builder.edit');
    Route::get('conta/{section?}', CustomerAccount::class)->name('account');
    Route::get('conta/favoritos', fn () => redirect()->route('account', ['section' => 'favorites']))->name('account.favorites');
    Route::get('encomendas/acompanhamento', OrderTracking::class)->name('orders.tracking');
    
});

Route::get('site/{site:slug}/{pageSlug?}', PublicSite::class)->name('site.public');

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', PlatformAdminDashboard::class)->name('dashboard');
        Route::get('websites', PlatformWebsites::class)->name('websites');
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

            session()->put('current_site_id', $site->id);
            return redirect()->route('site.manage.dashboard');
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
                    'metadata' => ['source' => 'platform_admin_exit'],
                ]);
            }
            session()->forget('platform_admin_site_id');

            return redirect()->route('admin.websites');
        })->name('websites.exit-access');
    });

Route::middleware(['auth', 'verified', 'site.access'])
    ->name('site.manage.')
    ->group(function (): void {
        Route::get('gestao', AdminDashboard::class)->name('dashboard');
        Route::get('config', StoreSettings::class)->name('config');
        Route::get('configuracao', SiteSettings::class)->name('settings');
        Route::get('definicoes', SiteDefinitions::class)->name('definitions');
        Route::get('media', MediaLibrary::class)->name('media');
        Route::get('media/{media}', function (SiteMedia $media) {
            abort_unless((int) $media->site_id === (int) session('current_site_id'), 404);
            abort_unless(Storage::disk($media->disk ?: 'public')->exists($media->path), 404);
            return response()->file(Storage::disk($media->disk ?: 'public')->path($media->path), [
                'Content-Type' => $media->mime_type ?: 'application/octet-stream',
                'Cache-Control' => 'public, max-age=3600',
            ]);
        })->name('media.file');
        Route::get('upgrade', PlanSelection::class)->name('upgrade');
        Route::get('menus', MenuManager::class)->name('menus');
        Route::get('categorias', CategoryManager::class)->name('categories');
        Route::get('pagamentos', PaymentManager::class)->name('payments');
        Route::get('promocoes', PromotionManager::class)->name('promotions');
        Route::get('stock', StockMovementManager::class)->name('stock');
        Route::get('submissoes', SiteSubmissions::class)->name('submissions');
        Route::get('mudar-website/{site:slug}', function (Site $site) {
            abort_unless($site->isManageableBy(auth()->user()), 403);
            session()->put('current_site_id', $site->id);
            return redirect()->route('site.manage.dashboard');
        })->withoutMiddleware('site.access')->name('switch');
    });

require __DIR__.'/settings.php';