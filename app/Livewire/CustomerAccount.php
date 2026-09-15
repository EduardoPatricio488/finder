<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.customer')]
class CustomerAccount extends Component
{
    public string $section = 'overview';

    public string $addressLabel = '';

    public string $addressText = '';

    public string $paymentType = 'cartao';

    public string $paymentLabel = '';

    public string $paymentLastFour = '';

    public bool $orderUpdates = true;

    public bool $promotions = true;

    public bool $stockAlerts = false;

    public function mount(?string $section = null): void
    {
        $this->section = $section ?? match (true) {
            request()->is('conta/favoritos') => 'favorites',
            request()->is('conta/moradas') => 'addresses',
            request()->is('conta/pagamentos') => 'payments',
            request()->is('conta/notificacoes') => 'notifications',
            default => 'overview',
        };

        $preferences = DB::table('customer_notification_preferences')->where('user_id', auth()->id())->first();
        if ($preferences !== null) {
            $this->orderUpdates = (bool) $preferences->order_updates;
            $this->promotions = (bool) $preferences->promotions;
            $this->stockAlerts = (bool) $preferences->stock_alerts;
        }
    }

    public function addAddress(): void
    {
        $validated = $this->validate(['addressLabel' => ['required', 'string', 'max:80'], 'addressText' => ['required', 'string', 'max:500']]);
        DB::table('customer_addresses')->insert(['user_id' => auth()->id(), 'label' => $validated['addressLabel'], 'address' => $validated['addressText'], 'created_at' => now(), 'updated_at' => now()]);
        $this->reset(['addressLabel', 'addressText']);
        session()->flash('account-status', 'Morada guardada.');
    }

    public function removeAddress(int $addressId): void
    {
        DB::table('customer_addresses')->where('id', $addressId)->where('user_id', auth()->id())->delete();
    }

    public function addPaymentMethod(): void
    {
        $validated = $this->validate(['paymentType' => ['required', 'in:cartao,mbway'], 'paymentLabel' => ['required', 'string', 'max:80'], 'paymentLastFour' => ['nullable', 'digits:4']]);
        DB::table('customer_payment_methods')->insert(['user_id' => auth()->id(), 'type' => $validated['paymentType'], 'label' => $validated['paymentLabel'], 'last_four' => $validated['paymentLastFour'], 'created_at' => now(), 'updated_at' => now()]);
        $this->reset(['paymentLabel', 'paymentLastFour']);
        session()->flash('account-status', 'Método de pagamento guardado.');
    }

    public function removePaymentMethod(int $paymentId): void
    {
        DB::table('customer_payment_methods')->where('id', $paymentId)->where('user_id', auth()->id())->delete();
    }

    public function saveNotifications(): void
    {
        DB::table('customer_notification_preferences')->updateOrInsert(['user_id' => auth()->id()], ['order_updates' => $this->orderUpdates, 'promotions' => $this->promotions, 'stock_alerts' => $this->stockAlerts, 'updated_at' => now(), 'created_at' => now()]);
        session()->flash('account-status', 'Preferências atualizadas.');
    }

    public function render(): mixed
    {
        $orders = Order::query()
            ->with('items')
            ->whereHas('customer', fn ($query) => $query->where('email', auth()->user()->email))
            ->latest('sold_at')
            ->get();

        $data = [
            'orders' => $orders,
            'totalSpent' => (float) $orders->where('status', '!=', 'cancelada')->sum('total'),
            'sections' => [
                'favorites' => ['title' => 'Favoritos', 'description' => 'Guarde produtos para voltar a encontrá-los mais tarde.', 'empty' => 'Ainda não tem produtos favoritos.'],
                'addresses' => ['title' => 'Moradas', 'description' => 'Tenha as suas moradas de entrega sempre à mão.', 'empty' => 'Ainda não tem moradas guardadas.'],
                'payments' => ['title' => 'Métodos de pagamento', 'description' => 'Consulte os seus métodos de pagamento guardados.', 'empty' => 'Ainda não tem métodos de pagamento guardados.'],
                'notifications' => ['title' => 'Notificações', 'description' => 'Escolha como quer receber novidades sobre as suas encomendas.', 'empty' => 'As suas preferências de notificação serão mostradas aqui.'],
            ],
        ];

        $data['currentSection'] = $data['sections'][$this->section] ?? $data['sections']['favorites'];

        if ($this->section !== 'overview') {
            $data['favorites'] = Product::query()->whereIn('id', DB::table('favorite_products')->where('user_id', auth()->id())->pluck('product_id'))->get();
            $data['addresses'] = DB::table('customer_addresses')->where('user_id', auth()->id())->latest()->get();
            $data['paymentMethods'] = DB::table('customer_payment_methods')->where('user_id', auth()->id())->latest()->get();

            return view('livewire.customer-account-section', $data);
        }

        return view('livewire.customer-account', $data);
    }
}
