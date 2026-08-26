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
use App\Livewire\UpgradeSelection;
use Illuminate\Support\Facades\Route;

// --- PORTAL PÚBLICO ---
Route::get('/', SiteDirectory::class)->name('home');
Route::get('planos', UpgradeSelection::class)->name('saas.upgrade');

// --- ROTA DE ENTRADA INTELIGENTE ---
Route::middleware(['auth', 'verified'])->get('entrada', function () {
    $user = auth()->user();

    // SE FOR ADMIN GLOBAL (admin@admin.pt) -> VAI PARA O DASHBOARD MESTRE
    if ($user->isAdministrator()) {
        return redirect()->route('dashboard');
    }

    // SE FOR UTILIZADOR COMUM -> VAI PARA O HUB DE SITES
    return redirect()->route('home');
})->name('entry');

// --- PAINEL ADMINISTRATIVO MESTRE (SÓ PARA TI) ---
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('dashboard', AdminDashboard::class)->name('dashboard');
    Route::get('admin/utilizadores', UserManager::class)->name('admin.users');
    Route::get('admin/config-global', StoreSettings::class)->name('admin.config');
});

// --- CRIAR NOVA LOJA ---
Route::middleware(['auth', 'verified'])->get('criar-loja', CreateSite::class)->name('site.create');

// --- PAINEL DO LOJISTA (ISOLADO POR SITE) ---
Route::middleware(['auth', 'verified', 'site.access'])
    ->prefix('admin/sites/{site:slug}')
    ->name('admin.site.')
    ->group(function () {
        Route::get('dashboard', AdminDashboard::class)->name('dashboard');
        Route::get('config', StoreSettings::class)->name('config');
        Route::get('upgrade', PlanSelection::class)->name('upgrade');
        Route::get('produtos', ProductManager::class)->name('products');
        Route::get('categorias', CategoryManager::class)->name('categories');
        Route::get('encomendas', OrderManager::class)->name('orders');
        Route::get('utilizadores', UserManager::class)->name('users');

        // PREMIUM
        Route::get('relatorios', Reports::class)->middleware('can:access-reports,site')->name('reports');
        Route::get('assistente', AdminAssistant::class)->middleware('can:access-ai,site')->name('assistant');
    });

require __DIR__.'/settings.php';
