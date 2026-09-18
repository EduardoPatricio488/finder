<?php

namespace App\Livewire;

use App\Models\Site;
use App\Models\SiteAnalyticsEvent;
use App\Models\SitePage;
use App\Models\SiteSubmission;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\StoreMailService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Website')]
class PublicSite extends Component
{
    public Site $site;

    public SitePage $page;

    public bool $preview = false;

    public string $contactName = '';

    public string $contactEmail = '';

    public string $contactMessage = '';

    public string $newsletterEmail = '';
    public array $cart = [];
    public bool $cartOpen = false;
    public bool $checkoutOpen = false;
    public string $customerName = '';
    public string $customerEmail = '';
    public string $customerPhone = '';
    public string $deliveryAddress = '';
    public string $paymentMethod = 'mbway';
    public ?string $completedOrderNumber = null;

    public function mount(Site $site, ?string $pageSlug = null): void
    {
        $this->preview = request()->boolean('preview');

        if ($this->preview) {
            abort_unless($site->isManageableBy(auth()->user()), 403);
        } else {
            abort_unless($site->is_published && $site->status === 'published', 404);
        }

        $this->site = $site->load(['menus.items.children.page', 'pages.sections', 'products.category', 'products.promotions', 'media']);
        $this->cart = session($this->cartKey(), []);
        $this->cartOpen = request()->query('cart') === '1';
        $this->customerName = (string) (auth()->user()?->name ?? '');
        $this->customerEmail = (string) (auth()->user()?->email ?? '');
        $this->page = $pageSlug
            ? $this->site->pages()->where('slug', $pageSlug)->when(! $this->preview, fn ($query) => $query->where('status', 'published'))->firstOrFail()
            : ($this->site->pages()->where('is_homepage', true)->when(! $this->preview, fn ($query) => $query->where('status', 'published'))->first()
                ?? $this->site->pages()->when(! $this->preview, fn ($query) => $query->where('status', 'published'))->orderBy('sort_order')->firstOrFail());

        if (! $this->preview) {
            SiteAnalyticsEvent::create([
                'site_id' => $this->site->id,
                'page_id' => $this->page->id,
                'event_type' => 'page_view',
                'path' => request()->path(),
                'referrer' => Str::limit((string) request()->headers->get('referer'), 500, ''),
                'device_type' => $this->deviceType(request()->userAgent()),
                'session_hash' => hash('sha256', (string) request()->session()->getId()),
                'occurred_at' => now(),
            ]);
        }
    }

    public function submitContact(): void
    {
        $this->ensurePublicSubmissionAllowed('contact');

        $validated = $this->validate([
            'contactName' => ['required', 'string', 'max:120'],
            'contactEmail' => ['required', 'email:rfc', 'max:255'],
            'contactMessage' => ['required', 'string', 'max:5000'],
        ]);

        SiteSubmission::create([
            'site_id' => $this->site->id,
            'form_type' => 'contact',
            'name' => $validated['contactName'],
            'email' => $validated['contactEmail'],
            'message' => $validated['contactMessage'],
            'status' => 'new',
        ]);

        $this->reset(['contactName', 'contactEmail', 'contactMessage']);
        session()->flash('site_form_success', 'A tua mensagem foi enviada com sucesso.');
    }

    public function subscribeNewsletter(): void
    {
        $this->ensurePublicSubmissionAllowed('newsletter');

        $validated = $this->validate([
            'newsletterEmail' => ['required', 'email:rfc', 'max:255'],
        ]);

        SiteSubmission::create([
            'site_id' => $this->site->id,
            'form_type' => 'newsletter',
            'email' => $validated['newsletterEmail'],
            'payload' => ['source' => 'newsletter'],
            'status' => 'new',
        ]);

        $this->reset('newsletterEmail');
        session()->flash('site_newsletter_success', 'Subscrição concluída com sucesso.');
    }

    private function cartKey(): string
    {
        return 'storefront_cart_'.$this->site->id;
    }

    public function addToCart(int $productId): void
    {
        $product = $this->site->products()->where('is_active', true)->findOrFail($productId);
        abort_if($product->stock < 1, 422, 'Este produto está esgotado.');
        $this->cart[$productId] = min(($this->cart[$productId] ?? 0) + 1, $product->stock);
        session()->put($this->cartKey(), $this->cart);
        $this->cartOpen = true;
    }

    public function increaseQuantity(int $productId): void { $this->addToCart($productId); }

    public function decreaseQuantity(int $productId): void
    {
        if (! isset($this->cart[$productId])) return;
        $this->cart[$productId] > 1 ? $this->cart[$productId]-- : unset($this->cart[$productId]);
        session()->put($this->cartKey(), $this->cart);
    }

    public function removeFromCart(int $productId): void
    {
        unset($this->cart[$productId]);
        session()->put($this->cartKey(), $this->cart);
    }

    public function openCheckout(): void
    {
        abort_if($this->cart === [], 422, 'O carrinho está vazio.');
        $this->cartOpen = false;
        $this->checkoutOpen = true;
    }

    public function checkout(): void
    {
        abort_if($this->cart === [], 422, 'O carrinho está vazio.');
        $validated = $this->validate([
            'customerName' => ['required', 'string', 'max:255'],
            'customerEmail' => ['required', 'email', 'max:255'],
            'customerPhone' => ['required', 'string', 'max:30'],
            'deliveryAddress' => ['required', 'string', 'max:1000'],
            'paymentMethod' => ['required', 'in:mbway,transferencia,cartao,entrega'],
        ]);
        $orderNumber = 'PED-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));

        DB::transaction(function () use ($validated, $orderNumber): void {
            $site = $this->site;
            $customer = $site->customers()->updateOrCreate(['email' => $validated['customerEmail']], ['name' => $validated['customerName'], 'phone' => $validated['customerPhone']]);
            $products = $site->products()->whereIn('id', array_keys($this->cart))->with('promotions')->lockForUpdate()->get()->keyBy('id');
            $subtotal = 0.0;
            foreach ($this->cart as $productId => $quantity) {
                $product = $products->get($productId);
                abort_if($product === null || ! $product->is_active || $product->stock < $quantity, 422, 'Um produto ficou sem stock.');
                $promotion = $product->promotions->first(fn ($p) => $p->starts_at <= now() && $p->ends_at >= now());
                $price = $promotion ? ($promotion->discount_type === 'percentagem' ? max(0, (float) $product->price * (1 - ((float) $promotion->discount_value / 100))) : max(0, (float) $product->price - (float) $promotion->discount_value)) : (float) $product->price;
                $subtotal += $price * $quantity;
            }
            $tax = $subtotal * 0.23;
            $shipping = $subtotal >= 50 ? 0 : 4.99;
            $total = $subtotal + $tax + $shipping;
            $order = $site->orders()->create(['order_number' => $orderNumber, 'customer_id' => $customer->id, 'delivery_address' => $validated['deliveryAddress'], 'sold_at' => now(), 'subtotal' => $subtotal, 'discount' => 0, 'tax' => $tax, 'shipping' => $shipping, 'total' => $total, 'payment_method' => $validated['paymentMethod'], 'status' => 'pendente']);
            foreach ($this->cart as $productId => $quantity) {
                $product = $products->get($productId);
                $promotion = $product->promotions->first(fn ($p) => $p->starts_at <= now() && $p->ends_at >= now());
                $price = $promotion ? ($promotion->discount_type === 'percentagem' ? max(0, (float) $product->price * (1 - ((float) $promotion->discount_value / 100))) : max(0, (float) $product->price - (float) $promotion->discount_value)) : (float) $product->price;
                $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'quantity' => $quantity, 'unit_price' => $price, 'total' => $price * $quantity]);
                $product->decrement('stock', $quantity);
                if ($product->stock <= $product->minimum_stock) app(StoreMailService::class)->lowStock($product->refresh());
            }
            $order->payments()->create(['site_id' => $site->id, 'amount' => $total, 'method' => $validated['paymentMethod'], 'status' => 'pendente']);
            app(StoreMailService::class)->orderReceived($order->load('customer'));
        });
        $this->completedOrderNumber = $orderNumber;
        $this->cart = [];
        session()->forget($this->cartKey());
        $this->checkoutOpen = false;
        $this->cartOpen = false;
    }

    public function getCartProductsProperty()
    {
        return $this->site->products()->whereIn('id', array_keys($this->cart))->with('promotions')->get();
    }

    public function getCartSubtotalProperty(): float
    {
        return $this->cartProducts->sum(function ($product) {
            $promotion = $product->promotions->first(fn ($p) => $p->starts_at <= now() && $p->ends_at >= now());
            $price = $promotion ? ($promotion->discount_type === 'percentagem' ? max(0, (float) $product->price * (1 - ((float) $promotion->discount_value / 100))) : max(0, (float) $product->price - (float) $promotion->discount_value)) : (float) $product->price;
            return $price * ($this->cart[$product->id] ?? 0);
        });
    }

    public function getCartShippingProperty(): float { return $this->cartSubtotal >= 50 ? 0 : ($this->cartSubtotal > 0 ? 4.99 : 0); }
    public function getCartTaxProperty(): float { return $this->cartSubtotal * 0.23; }
    public function getCartTotalProperty(): float { return $this->cartSubtotal + $this->cartTax + $this->cartShipping; }

    private function ensurePublicSubmissionAllowed(string $type): void
    {
        abort_if($this->preview, 404);

        $key = sprintf('site-submission:%s:%s:%s', $this->site->id, $type, request()->ip());
        abort_if(RateLimiter::tooManyAttempts($key, 5), 429);
        RateLimiter::hit($key, 60);
    }

    private function deviceType(?string $userAgent): string
    {
        if (! $userAgent) {
            return 'unknown';
        }

        if (preg_match('/tablet|ipad/i', $userAgent)) {
            return 'tablet';
        }

        if (preg_match('/mobile|android|iphone/i', $userAgent)) {
            return 'mobile';
        }

        return 'desktop';
    }

    public function render(): mixed
    {
        $view = $this->site->type === 'personal'
            ? 'livewire.personal-site-renderer'
            : 'livewire.public-site-renderer';

        $media = $this->site->media
            ->filter(fn ($item) => filled($item->placement))
            ->groupBy('placement');

        return view($view, [
            'mediaByPlacement' => $media,
        ])
            ->layout('layouts.public-site', [
                'site' => $this->site,
                'page' => $this->page,
                'preview' => $this->preview,
            ]);
    }
}
