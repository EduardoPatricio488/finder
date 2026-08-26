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
use App\Livewire\CreateSite;
use App\Livewire\PlanSelection;
use Illuminate\Support\Facades\Route;

// --- PORTAL PÚBLICO (DIRETÓRIO) ---
Route::get('/', SiteDirectory::class)->name('home');

// --- ACESSO ÀS LOJAS DOS CLIENTES ---
Route::get('sites/{site:slug}', SiteWorkspace::class)->name('sites.show');
Route::scopeBindings()->group(function (): void {
    Route::view('sites/{site:slug}/loja', 'landing')->name('sites.store');
    Route::get('sites/{site:slug}/produtos', ProductCatalog::class)->name('sites.products');
    Route::get('sites/{site:slug}/produtos/{product:slug}', ProductDetail::class)->name('sites.products.show');
});

// --- ÁREA DO CLIENTE (COMPRADOR) ---
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('minhas-encomendas', OrderTracking::class)->name('orders.tracking');
    Route::get('minha-conta', CustomerAccount::class)->name('account');
    Route::get('minha-conta/{section}', CustomerAccount::class)->name('account.section');
});

// --- ROTA DE ENTRADA (SEMPRE HUB) ---
Route::middleware(['auth', 'verified'])->get('entrada', function () {
    return redirect()->route('home');
})->name('entry');

// --- CRIAR NOVA LOJA ---
Route::middleware(['auth', 'verified'])->get('criar-loja', CreateSite::class)->name('site.create');

// --- ADMINISTRAÇÃO MESTRE ---
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('dashboard', AdminDashboard::class)->name('dashboard');
    Route::get('admin/utilizadores', UserManager::class)->name('admin.users');
});

// --- PAINEL DO LOJISTA (SaaS) ---
Route::middleware(['auth', 'verified', 'site.access'])
    ->prefix('admin/sites/{site:slug}')
    ->name('admin.site.')
    ->group(function () {

        Route::get('dashboard', AdminDashboard::class)->name('dashboard');
        Route::get('config', StoreSettings::class)->name('config');
        Route::get('upgrade', PlanSelection::class)->name('upgrade');
        Route::get('produtos', ProductManager::class)->name('products');
        Route::get('categorias', CategoryManager::class)->name('categories');
        Route::get('stock/movements', StockMovementManager::class)->name('stock');
        Route::get('encomendas', OrderManager::class)->name('orders');
        Route::get('clientes', CustomerManager::class)->name('customers');
        Route::get('promocoes', PromotionManager::class)->name('promotions');
        Route::get('vendas', SalesManager::class)->name('sales');
        Route::get('pagamentos', PaymentManager::class)->name('payments');

        // PREMIUM
        Route::get('relatorios', Reports::class)->middleware('can:access-reports,site')->name('reports');
        Route::get('assistente', AdminAssistant::class)->middleware('can:access-ai,site')->name('assistant');
    });

require __DIR__.'/settings.php';
