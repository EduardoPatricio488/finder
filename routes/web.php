<?php

use App\Livewire\AdminAssistant;
use App\Livewire\AdminDashboard;
use App\Livewire\BuilderEditor;
use App\Livewire\CategoryManager;
use App\Livewire\CreateSite;
use App\Livewire\LandingPage;
use App\Livewire\OrderManager;
use App\Livewire\PlanSelection;
use App\Livewire\ProductManager;
use App\Livewire\PublicSite;
use App\Livewire\Reports;
use App\Livewire\StoreSettings;
use App\Livewire\UpgradeSelection;
use App\Livewire\UserDashboard;
use App\Livewire\UserManager;
use Illuminate\Support\Facades\Route;

// --- LANDING PAGE ---
Route::get('/', LandingPage::class)->name('home');
Route::get('planos', UpgradeSelection::class)->name('saas.upgrade');

// --- ENTRADA APÓS LOGIN ---
Route::middleware(['auth', 'verified'])->get('entrada', function () {
    return redirect()->route('dashboard');
})->name('entry');

// --- DASHBOARD DA PLATAFORMA ---
Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('dashboard', UserDashboard::class)->name('dashboard');
    Route::get('websites/create', CreateSite::class)->name('site.create');
    Route::get('websites/{site:slug}/builder', BuilderEditor::class)->name('builder.edit');
});

// --- ADMIN GLOBAL DA PLATAFORMA ---
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('platform.admin.')->group(function (): void {
    Route::get('/', AdminDashboard::class)->name('dashboard');
    Route::get('utilizadores', UserManager::class)->name('users');
    Route::get('websites', UserManager::class)->name('websites');
    Route::get('configuracoes', StoreSettings::class)->name('settings');
});

// --- ADMIN DO WEBSITE ---
Route::middleware(['auth', 'verified', 'site.access'])
    ->prefix('admin/sites/{site:slug}')
    ->name('admin.site.')
    ->group(function (): void {
        Route::get('dashboard', AdminDashboard::class)->name('dashboard');
        Route::get('config', StoreSettings::class)->name('config');
        Route::get('upgrade', PlanSelection::class)->name('upgrade');
        Route::get('produtos', ProductManager::class)->name('products');
        Route::get('categorias', CategoryManager::class)->name('categories');
        Route::get('encomendas', OrderManager::class)->name('orders');
        Route::get('utilizadores', UserManager::class)->name('users');
        Route::get('relatorios', Reports::class)->middleware('can:access-reports,site')->name('reports');
        Route::get('assistente', AdminAssistant::class)->middleware('can:access-ai,site')->name('assistant');
    });

// --- WEBSITE PÚBLICO ---
Route::get('site/{site:slug}/{pageSlug?}', PublicSite::class)->name('site.public');

require __DIR__.'/settings.php';
