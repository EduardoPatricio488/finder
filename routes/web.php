<?php

use App\Livewire\AdminAssistant;
use App\Livewire\AdminDashboard;
use App\Livewire\CategoryManager;
use App\Livewire\CustomerAccount;
use App\Livewire\CustomerManager;
use App\Livewire\OrderManager;
use App\Livewire\OrderTracking;
use App\Livewire\PaymentManager;
use App\Livewire\ProductCatalog;
use App\Livewire\ProductDetail;
use App\Livewire\ProductManager;
use App\Livewire\PromotionManager;
use App\Livewire\Reports;
use App\Livewire\SalesManager;
use App\Livewire\SiteDirectory;
use App\Livewire\SiteWorkspace;
use App\Livewire\StockMovementManager;
use App\Livewire\StoreSettings;
use App\Livewire\UserManager;
use Illuminate\Support\Facades\Route;

Route::get('/', SiteDirectory::class)->name('home');
Route::get('sites/{site:slug}', SiteWorkspace::class)->name('sites.show');
Route::scopeBindings()->group(function (): void {
    Route::view('sites/{site:slug}/loja', 'landing')->name('sites.store');
    Route::get('sites/{site:slug}/produtos', ProductCatalog::class)->name('sites.products');
    Route::get('sites/{site:slug}/produtos/{product:slug}', ProductDetail::class)->name('sites.products.show');
});
Route::view('vendas', 'landing')->name('sales');
Route::get('produtos', ProductCatalog::class)->name('products');
Route::get('produtos/{product:slug}', ProductDetail::class)->name('products.show');

Route::middleware(['auth', 'verified'])->get('minhas-encomendas', OrderTracking::class)->name('orders.tracking');
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('minha-conta', CustomerAccount::class)->name('account');
    Route::get('minha-conta/favoritos', CustomerAccount::class)->defaults('section', 'favorites')->name('account.favorites');
    Route::get('minha-conta/moradas', CustomerAccount::class)->defaults('section', 'addresses')->name('account.addresses');
    Route::get('minha-conta/pagamentos', CustomerAccount::class)->defaults('section', 'payments')->name('account.payments');
    Route::get('minha-conta/notificacoes', CustomerAccount::class)->defaults('section', 'notifications')->name('account.notifications');
});

Route::middleware(['auth', 'verified'])->get('entrada', function () {
    return redirect()->route(auth()->user()->isAdministrator() ? 'dashboard' : 'sales');
})->name('entry');

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('dashboard', AdminDashboard::class)->name('dashboard');

    Route::get('admin/config', StoreSettings::class)->name('admin.config');
    Route::get('admin/produtos', ProductManager::class)->name('admin.products');
    Route::get('admin/promocoes', PromotionManager::class)->name('admin.promotions');
    Route::get('admin/utilizadores', UserManager::class)->name('admin.users');
    Route::get('admin/clientes', CustomerManager::class)->name('admin.customers');
    Route::get('admin/vendas', SalesManager::class)->name('admin.sales');
    Route::get('admin/encomendas', OrderManager::class)->name('admin.orders');
    Route::get('admin/pagamentos', PaymentManager::class)->name('admin.payments');
    Route::get('admin/stock/movimentos', StockMovementManager::class)->name('admin.stock');
    Route::get('admin/relatorios', Reports::class)->name('admin.reports');
    Route::get('admin/assistente', AdminAssistant::class)->name('admin.assistant');
    Route::get('admin/categorias', CategoryManager::class)->name('admin.categories');
});

Route::middleware(['auth', 'verified', 'site.access'])
    ->prefix('admin/sites/{site:slug}')
    ->name('admin.site.')
    ->group(function () {
        Route::get('dashboard', AdminDashboard::class)->name('dashboard');
        Route::get('config', StoreSettings::class)->name('config');
        Route::get('produtos', ProductManager::class)->name('products');
        Route::get('promocoes', PromotionManager::class)->name('promotions');
        Route::get('utilizadores', UserManager::class)->name('users');
        Route::get('clientes', CustomerManager::class)->name('customers');
        Route::get('vendas', SalesManager::class)->name('sales');
        Route::get('encomendas', OrderManager::class)->name('orders');
        Route::get('pagamentos', PaymentManager::class)->name('payments');
        Route::get('stock/movimentos', StockMovementManager::class)->name('stock');
        Route::get('relatorios', Reports::class)->name('reports');
        Route::get('assistente', AdminAssistant::class)->name('assistant');
        Route::get('categorias', CategoryManager::class)->name('categories');
    });

require __DIR__.'/settings.php';
