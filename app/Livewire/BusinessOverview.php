<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithSiteContext;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Visão geral')]
class BusinessOverview extends Component
{
    use InteractsWithSiteContext;

    public function render(): mixed
    {
        $site = $this->currentSite();
        abort_unless($site->isManageableBy(auth()->user()), 403);

        $orders = Order::query()->where('site_id', $site->id);
        $payments = Payment::query()->whereHas('order', fn ($query) => $query->where('site_id', $site->id));
        $products = Product::query()->where('site_id', $site->id);

        $paidStatuses = ['pago', 'em_preparacao', 'enviado', 'entregue'];
        $validOrders = (clone $orders)->whereNotIn('status', ['cancelada']);
        $paidOrders = (clone $orders)->whereIn('status', $paidStatuses);
        $todayOrders = (clone $validOrders)->whereDate('sold_at', today());
        $monthOrders = (clone $validOrders)->whereBetween('sold_at', [now()->startOfMonth(), now()->endOfMonth()]);

        $monthlySales = collect(range(5, 0))->map(function (int $monthsAgo) use ($site): array {
            $date = now()->startOfMonth()->subMonths($monthsAgo);
            return [
                'label' => $date->translatedFormat('M'),
                'total' => (float) Order::query()->where('site_id', $site->id)
                    ->whereNotIn('status', ['cancelada'])
                    ->whereBetween('sold_at', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
                    ->sum('total'),
            ];
        });
        $maxMonthlySales = max(1, (float) $monthlySales->max('total'));

        $recentOrders = (clone $orders)->with(['customer', 'payments'])
            ->latest('sold_at')->limit(6)->get();

        $topProducts = Product::query()->where('site_id', $site->id)
            ->withCount(['orderItems as units_sold' => function ($query) use ($site) {
                $query->whereHas('order', fn ($ordersQuery) => $ordersQuery
                    ->where('site_id', $site->id)
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
