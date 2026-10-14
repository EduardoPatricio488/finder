<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithSiteContext;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Site;
use App\Support\SiteContext;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Visão geral')]
class BusinessOverview extends Component
{
    use InteractsWithSiteContext;

    public ?string $selectedSiteId = null;

    public function mount(): void
    {
        $requestedSite = request()->query('site');
        $this->selectedSiteId = filled($requestedSite) ? (string) $requestedSite : null;
    }

    private function manageableSitesQuery()
    {
        return SiteContext::manageableSitesQuery(auth()->user())
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    private function selectedSite(): ?Site
    {
        if (blank($this->selectedSiteId)) {
            return null;
        }

        return $this->manageableSitesQuery()->whereKey((int) $this->selectedSiteId)->firstOrFail();
    }

    public function updatedSelectedSiteId(): void
    {
        if (blank($this->selectedSiteId)) {
            $this->selectedSiteId = null;

            return;
        }

        $this->selectedSite();
    }

    public function render(): mixed
    {
        $sites = $this->manageableSitesQuery()->get();
        $site = $this->selectedSite();
        $siteIds = $site ? collect([$site->id]) : $sites->pluck('id');

        abort_unless($siteIds->isNotEmpty(), 404);

        $orders = Order::query()->whereIn('site_id', $siteIds);
        $payments = Payment::query()->whereHas('order', fn ($query) => $query->whereIn('site_id', $siteIds));
        $products = Product::query()->whereIn('site_id', $siteIds);

        $paidStatuses = ['pago', 'em_preparacao', 'enviado', 'entregue'];
        $validOrders = (clone $orders)->whereNotIn('status', ['cancelada']);
        $paidOrders = (clone $orders)->whereIn('status', $paidStatuses);
        $todayOrders = (clone $validOrders)->whereDate('sold_at', today());
        $monthOrders = (clone $validOrders)->whereBetween('sold_at', [now()->startOfMonth(), now()->endOfMonth()]);

        $monthlySales = collect(range(5, 0))->map(function (int $monthsAgo) use ($siteIds): array {
            $date = now()->startOfMonth()->subMonths($monthsAgo);

            return [
                'label' => $date->translatedFormat('M'),
                'total' => (float) Order::query()->whereIn('site_id', $siteIds)
                    ->whereNotIn('status', ['cancelada'])
                    ->whereBetween('sold_at', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
                    ->sum('total'),
            ];
        });
        $maxMonthlySales = max(1, (float) $monthlySales->max('total'));

        $recentOrders = (clone $orders)->with(['customer', 'payments'])
            ->latest('sold_at')->limit(6)->get();

        $topProducts = Product::query()->whereIn('site_id', $siteIds)
            ->withCount(['orderItems as units_sold' => function ($query) use ($siteIds) {
                $query->whereHas('order', fn ($ordersQuery) => $ordersQuery
                    ->whereIn('site_id', $siteIds)
                    ->whereNotIn('status', ['cancelada']));
            }])
            ->orderByDesc('units_sold')->limit(5)->get();

        $paymentStatusCounts = [
            'Recebidos' => (clone $payments)->where('status', 'recebido')->count(),
            'Pendentes' => (clone $payments)->where('status', 'pendente')->count(),
            'Falhados' => (clone $payments)->whereIn('status', ['falhado', 'recusado', 'cancelado'])->count(),
        ];

        return view('livewire.business-overview', [
            'site' => $site,
            'sites' => $sites,
            'allSitesMode' => $site === null,
            'totalProducts' => (clone $products)->count(),
            'activeProducts' => (clone $products)->where('is_active', true)->count(),
            'lowStockProducts' => (clone $products)->whereColumn('stock', '<=', 'minimum_stock')->count(),
            'totalOrders' => (clone $validOrders)->count(),
            'pendingOrders' => (clone $orders)->whereIn('status', ['pendente', 'em_preparacao'])->count(),
            'todayRevenue' => (float) (clone $todayOrders)->whereIn('status', $paidStatuses)->sum('total'),
            'monthRevenue' => (float) (clone $monthOrders)->whereIn('status', $paidStatuses)->sum('total'),
            'allRevenue' => (float) (clone $paidOrders)->sum('total'),
            'todaySales' => (clone $todayOrders)->count(),
            'monthSales' => (clone $monthOrders)->count(),
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
            'monthlySales' => $monthlySales,
            'maxMonthlySales' => $maxMonthlySales,
            'paymentStatusCounts' => $paymentStatusCounts,
            'totalPayments' => array_sum($paymentStatusCounts),
        ]);
    }
}
