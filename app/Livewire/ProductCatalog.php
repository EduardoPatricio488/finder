<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Services\FinderNotificationService;
use App\Models\Site;
use App\Services\StoreMailService;
use App\Support\SiteContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class ProductCatalog extends Component
{
    use WithFileUploads;

    public string $search = '';
    public string $category = '';
    public string $minPrice = '';
    public string $maxPrice = '';
    public string $availability = '';
    public string $minRating = '';
    public bool $productModalOpen = false;
    public bool $manageProductModalOpen = false;
    public bool $imageGalleryOpen = false;
    public string $imageGalleryName = '';
    public array $imageGalleryPhotos = [];
    public int $imageGalleryIndex = 0;
    public ?int $manageProductId = null;
    public string $productName = '';
    public string $productDescription = '';
    public string $productPrice = '';
    public ?int $productCategoryId = null;
    public string $productCustomCategory = '';
    public int $productStock = 0;
    public int $productMinimumStock = 0;
    public bool $productIsActive = true;
    public array $productImages = [];
    public array $cart = [];
    public string $couponCode = '';
    public ?string $appliedCouponCode = null;
    public float $couponDiscount = 0;
    public string $couponMessage = '';
    public bool $cartOpen = false;
    public bool $checkoutOpen = false;
    public string $customerName = '';
    public string $customerEmail = '';
    public string $customerPhone = '';
    public string $deliveryAddress = '';
    public string $paymentMethod = 'mbway';
    public ?string $completedOrderNumber = null;

    private function site(): Site { return SiteContext::storefront(); }
    private function cartKey(): string { return 'catalog_cart_'.$this->site()->id; }

    private function siteScoped(Builder $query): Builder
    {
        $site = $this->site();
        if (! Schema::hasColumn($query->getModel()->getTable(), 'site_id')) return $query;
        return $query->where('site_id', $site->id);
    }

    public function mount(): mixed
    {
        $referer = (string) request()->headers->get('referer', '');
        if (preg_match('~/websites/([^/]+)/builder(?:[/?#]|$)~', $referer, $matches)) {
            $site = Site::query()->where('slug', $matches[1])->first();
            if ($site !== null) return redirect()->route('admin.site.products', $site);
        }
        $this->cart = session($this->cartKey(), []);
        $this->cartOpen = request()->query('cart') === '1';
        if (! auth()->check()) return null;
        $user = auth()->user();
        $this->customerName = $user->name;
        $this->customerEmail = $user->email;
        $this->customerPhone = (string) $this->site()->customers()->where('email', $user->email)->value('phone');
        return null;
    }

    public function updatedProductMinimumStock(): void
    {
        $this->validateMinimumStock();
    }

    public function updatedProductStock(): void
    {
        $this->validateMinimumStock();
    }

    private function validateMinimumStock(): void
    {
        $this->resetErrorBag('productMinimumStock');

        if ($this->productMinimumStock < 0 || $this->productStock < 0) {
            return;
        }

        if ($this->productMinimumStock >= $this->productStock) {
            $this->addError(
                'productMinimumStock',
                'O stock mínimo tem de ser inferior ao stock atual.'
            );
        }
    }

    public function openProductModal(): void
    {
        $this->ensureGenericCategories();
        $this->resetProductForm();
        $this->productModalOpen = true;
    }

    private function ensureGenericCategories(): void
    {
        $site = $this->site();

        $categories = [
            'Alimentação',
            'Bebidas',
            'Casa',
            'Desporto',
            'Eletrónica',
            'Moda',
            'Saúde e Beleza',
            'Serviços',
            'Tecnologia',
            'Transportes',
            'Outros',
        ];

        foreach ($categories as $name) {
            $site->categories()->firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }
    }

    public function openImageGallery(int $productId, int $index = 0): void
    {
        $product = $this->siteScoped(Product::query())->findOrFail($productId);
        $photos = $product->images ?: ($product->image_url ? [$product->image_url] : []);

        abort_if($photos === [], 404);

        $this->imageGalleryName = (string) $product->name;
        $this->imageGalleryPhotos = array_values($photos);
        $this->imageGalleryIndex = max(0, min($index, count($this->imageGalleryPhotos) - 1));
        $this->imageGalleryOpen = true;
    }

    public function closeImageGallery(): void
    {
        $this->imageGalleryOpen = false;
        $this->imageGalleryPhotos = [];
        $this->imageGalleryName = '';
        $this->imageGalleryIndex = 0;
    }

    public function nextImage(): void
    {
        if ($this->imageGalleryPhotos === []) {
            return;
        }

        $this->imageGalleryIndex = ($this->imageGalleryIndex + 1) % count($this->imageGalleryPhotos);
    }

    public function previousImage(): void
    {
        if ($this->imageGalleryPhotos === []) {
            return;
        }

        $this->imageGalleryIndex = ($this->imageGalleryIndex - 1 + count($this->imageGalleryPhotos)) % count($this->imageGalleryPhotos);
    }

    public function openManageProductModal(int $productId): void
    {
        $this->ensureGenericCategories();
        $product = $this->siteScoped(Product::query())->with('category')->findOrFail($productId);

        $this->manageProductId = $product->id;
        $this->productName = (string) $product->name;
        $this->productDescription = (string) ($product->description ?? '');
        $this->productPrice = (string) $product->price;
        $this->productCategoryId = $product->category_id;
        $this->productCustomCategory = '';
        $this->productStock = (int) $product->stock;
        $this->productMinimumStock = (int) $product->minimum_stock;
        $this->productIsActive = (bool) $product->is_active;
        $this->productImages = [];
        $this->resetValidation();
        $this->manageProductModalOpen = true;
    }

    public function closeManageProductModal(): void
    {
        $this->manageProductModalOpen = false;
        $this->manageProductId = null;
        $this->resetProductForm();
    }

    public function updateProduct(): void
    {
        $data = $this->validate([
            'productName' => ['required', 'string', 'max:255'],
            'productDescription' => ['nullable', 'string'],
            'productPrice' => ['required', 'numeric', 'min:0'],
            'productCategoryId' => ['required', 'integer'],
            'productCustomCategory' => ['nullable', 'string', 'max:255'],
            'productStock' => ['required', 'integer', 'min:0'],
            'productMinimumStock' => ['required', 'integer', 'min:0', 'lt:productStock'],
            'productIsActive' => ['boolean'],
            'productImages' => ['nullable', 'array', 'max:10'],
            'productImages.*' => ['image', 'max:10240'],
        ]);

        $site = $this->site();
        $product = $this->siteScoped(Product::query())->findOrFail($this->manageProductId);
        $category = $site->categories()->findOrFail($data['productCategoryId']);

        if ($category->slug === 'outros' && trim($data['productCustomCategory']) !== '') {
            $customName = trim($data['productCustomCategory']);
            $category = $site->categories()->firstOrCreate(
                ['slug' => Str::slug($customName)],
                ['name' => $customName],
            );
        }

        $product->update([
            'category_id' => $category->id,
            'name' => $data['productName'],
            'description' => $data['productDescription'],
            'price' => $data['productPrice'],
            'is_active' => $data['productIsActive'],
            'stock' => $data['productStock'],
            'minimum_stock' => $data['productMinimumStock'],
        ]);

        if ($this->productImages !== []) {
            $paths = $product->images ?? [];
            foreach ($this->productImages as $image) {
                $paths[] = $image->store('products', 'public');
            }
            $product->update([
                'image_url' => $paths[0] ?? null,
                'images' => $paths,
            ]);
        }

        $this->closeManageProductModal();
        session()->flash('status', 'Produto atualizado com sucesso.');
        app(FinderNotificationService::class)->productChanged($site, 'Foi atualizado o produto "'.$product->name.'".');
    }

    public function closeProductModal(): void
    {
        $this->productModalOpen = false;
        $this->resetProductForm();
    }

    public function saveProduct(): void
    {
        $data = $this->validate([
            'productName' => ['required', 'string', 'max:255'],
            'productDescription' => ['nullable', 'string'],
            'productPrice' => ['required', 'numeric', 'min:0'],
            'productCategoryId' => ['required', 'integer'],
            'productCustomCategory' => ['nullable', 'string', 'max:255'],
            'productStock' => ['required', 'integer', 'min:0'],
            'productMinimumStock' => ['required', 'integer', 'min:0', 'lt:productStock'],
            'productIsActive' => ['boolean'],
            'productImages' => ['nullable', 'array', 'max:10'],
            'productImages.*' => ['image', 'max:10240'],
        ]);

        $site = $this->site();
        abort_unless($site->categories()->whereKey($data['productCategoryId'])->exists(), 404);

        $category = $site->categories()->findOrFail($data['productCategoryId']);
        if ($category->slug === 'outros' && trim($data['productCustomCategory']) !== '') {
            $customName = trim($data['productCustomCategory']);
            $category = $site->categories()->firstOrCreate(
                ['slug' => Str::slug($customName)],
                ['name' => $customName],
            );
        }

        $product = $site->products()->create([
            'category_id' => $category->id,
            'name' => $data['productName'],
            'slug' => Str::slug($data['productName']).'-'.Str::lower(Str::random(5)),
            'description' => $data['productDescription'],
            'price' => $data['productPrice'],
            'is_active' => $data['productIsActive'],
            'stock' => $data['productStock'],
            'minimum_stock' => $data['productMinimumStock'],
        ]);

        if ($this->productImages !== []) {
            $paths = [];
            foreach ($this->productImages as $image) {
                $paths[] = $image->store('products', 'public');
            }
            $product->update([
                'image_url' => $paths[0] ?? null,
                'images' => $paths,
            ]);
        }

        $this->productModalOpen = false;
        $this->resetProductForm();
        session()->flash('status', 'Produto adicionado com sucesso.');
        app(FinderNotificationService::class)->productChanged($site, 'Foi adicionado o produto "'.$product->name.'".');
    }

    private function resetProductForm(): void
    {
        $this->reset([
            'productName',
            'productDescription',
            'productPrice',
            'productCategoryId',
            'productCustomCategory',
            'productImages',
            'productStock',
            'productMinimumStock',
        ]);
        $this->productIsActive = true;
        $this->resetValidation();
    }

    private function persistCart(): void { session()->put($this->cartKey(), $this->cart); }

    public function addToCart(int $productId): void
    {
        $product = $this->siteScoped(Product::query())->where('is_active', true)->where('stock', '>', 0)->findOrFail($productId);
        $this->cart[$productId] = min(($this->cart[$productId] ?? 0) + 1, $product->stock);
        $this->persistCart();
        $this->cartOpen = true;
    }

    public function toggleFavorite(int $productId): void
    {
        abort_unless(auth()->check(), 403);
        abort_unless($this->siteScoped(Product::query())->whereKey($productId)->exists(), 404);
        $favorite = DB::table('favorite_products')->where('site_id', $this->site()->id)->where('user_id', auth()->id())->where('product_id', $productId);
        $favorite->exists() ? $favorite->delete() : DB::table('favorite_products')->insert(['site_id' => $this->site()->id, 'user_id' => auth()->id(), 'product_id' => $productId]);
    }

    public function removeFromCart(int $productId): void { unset($this->cart[$productId]); $this->persistCart(); }
    public function increaseQuantity(int $productId): void { $this->addToCart($productId); }

    public function decreaseQuantity(int $productId): void
    {
        if (! isset($this->cart[$productId])) return;
        $this->cart[$productId] > 1 ? $this->cart[$productId]-- : $this->removeFromCart($productId);
        $this->persistCart();
    }

    public function applyCoupon(): void
    {
        $this->resetErrorBag('couponCode');
        $site = $this->site();
        $coupon = DB::table('coupons')
            ->when(Schema::hasColumn('coupons', 'site_id'), fn ($query) => $query->where('site_id', $site->id))
            ->whereRaw('upper(code) = ?', [strtoupper(trim($this->couponCode))])
            ->where('starts_at', '<=', now())->where('ends_at', '>=', now())->first();

        if ($coupon === null || ($coupon->maximum_uses !== null && $coupon->uses >= $coupon->maximum_uses) || $this->cartSubtotal < (float) $coupon->minimum_order_value) {
            $this->appliedCouponCode = null; $this->couponDiscount = 0; $this->couponMessage = '';
            $this->addError('couponCode', 'Cupão inválido, expirado ou não aplicável a este pedido.');
            return;
        }
        $this->appliedCouponCode = $coupon->code;
        $this->couponDiscount = $coupon->type === 'percentagem' ? min($this->cartSubtotal, $this->cartSubtotal * ((float) $coupon->value / 100)) : min($this->cartSubtotal, (float) $coupon->value);
        $this->couponMessage = 'Cupão aplicado com sucesso.';
        $this->couponCode = $coupon->code;
    }

    public function openCheckout(): void { if ($this->cart !== []) $this->checkoutOpen = true; }

    public function checkout(): void
    {
        $validated = $this->validate([
            'customerName' => ['required', 'string', 'max:255'],
            'customerEmail' => ['required', 'email', 'max:255'],
            'customerPhone' => ['required', 'string', 'max:30'],
            'deliveryAddress' => ['required', 'string', 'max:1000'],
            'paymentMethod' => ['required', 'in:mbway,transferencia,cartao,entrega'],
        ]);
        $orderNumber = 'PED-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));

        DB::transaction(function () use ($validated, $orderNumber): void {
            $site = $this->site();
            $customer = $site->customers()->updateOrCreate(['email' => $validated['customerEmail']], ['name' => $validated['customerName'], 'phone' => $validated['customerPhone']]);
            $products = $this->siteScoped(Product::query())->whereIn('id', array_keys($this->cart))->with(['promotions' => fn ($query) => $query->where('starts_at', '<=', now())->where('ends_at', '>=', now())])->lockForUpdate()->get()->keyBy('id');
            $subtotal = 0.0;
            foreach ($this->cart as $productId => $quantity) {
                $product = $products->get($productId);
                abort_if($product === null || ! $product->is_active || $product->stock < $quantity, 422, 'Um produto ficou sem stock.');
                $subtotal += $this->salePrice($product) * $quantity;
            }
            $discount = min($subtotal, $this->couponDiscount);
            $taxable = $subtotal - $discount; $tax = $taxable * 0.23; $shipping = $taxable >= 50 ? 0 : 4.99; $total = $taxable + $tax + $shipping;
            $order = $site->orders()->create(['order_number' => $orderNumber, 'customer_id' => $customer->id, 'delivery_address' => $validated['deliveryAddress'], 'sold_at' => now(), 'subtotal' => $subtotal, 'discount' => $discount, 'tax' => $tax, 'shipping' => $shipping, 'total' => $total, 'payment_method' => $validated['paymentMethod'], 'status' => 'pendente']);
            foreach ($this->cart as $productId => $quantity) {
                $product = $products->get($productId);
                $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'quantity' => $quantity, 'unit_price' => $this->salePrice($product), 'total' => $this->salePrice($product) * $quantity]);
                $product->decrement('stock', $quantity);
                if ($product->stock <= $product->minimum_stock) app(StoreMailService::class)->lowStock($product->refresh());
            }
            $order->payments()->create(['site_id' => $site->id, 'amount' => $total, 'method' => $validated['paymentMethod'], 'status' => 'pendente']);
            app(StoreMailService::class)->orderReceived($order->load('customer'));
        });

        $this->completedOrderNumber = $orderNumber; $this->cart = []; session()->forget($this->cartKey());
        $this->couponCode = ''; $this->appliedCouponCode = null; $this->couponDiscount = 0; $this->couponMessage = '';
        $this->checkoutOpen = false; $this->cartOpen = false; $this->reset(['customerName', 'customerEmail', 'customerPhone']);
    }

    public function getCartProductsProperty(): Collection
    {
        if ($this->cart === []) return collect();
        return $this->siteScoped(Product::query())->whereIn('id', array_keys($this->cart))->with(['promotions' => fn ($query) => $query->where('starts_at', '<=', now())->where('ends_at', '>=', now())])->get();
    }

    public function getCartTotalProperty(): float { return $this->cartSubtotal - $this->couponDiscount + $this->vat + $this->shipping; }
    public function getCartSubtotalProperty(): float { return $this->cartProducts->sum(fn (Product $product): float => $this->salePrice($product) * ($this->cart[$product->id] ?? 0)); }

    public function salePrice(Product $product): float
    {
        $promotion = $product->promotions->first();
        if ($promotion === null) return (float) $product->price;
        return $promotion->discount_type === 'percentagem' ? max(0, (float) $product->price * (1 - ((float) $promotion->discount_value / 100))) : max(0, (float) $product->price - (float) $promotion->discount_value);
    }

    public function getVatProperty(): float { return max(0, $this->cartSubtotal - $this->couponDiscount) * 0.23; }
    public function getShippingProperty(): float { return $this->cartSubtotal - $this->couponDiscount >= 50 ? 0 : ($this->cartSubtotal > 0 ? 4.99 : 0); }

    public function render(): mixed
    {
        $site = $this->site(); $minimumRating = (float) $this->minRating;
        $products = $this->siteScoped(Product::query())->where('is_active', true)->with('category')->with(['promotions' => fn ($query) => $query->where('starts_at', '<=', now())->where('ends_at', '>=', now())])->withAvg('reviews', 'rating')->withCount('reviews')
            ->when($this->search !== '', fn ($query) => $query->where(fn ($query) => $query->where('name', 'like', '%'.$this->search.'%')->orWhere('description', 'like', '%'.$this->search.'%')->orWhere('sku', 'like', '%'.$this->search.'%')->orWhere('price', 'like', '%'.$this->search.'%')->orWhere('tags', 'like', '%'.$this->search.'%')->orWhereHas('category', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))))
            ->when($this->category !== '', fn ($query) => $query->whereHas('category', fn ($query) => $query->where('slug', $this->category)))
            ->when($this->minPrice !== '', fn ($query) => $query->where('price', '>=', (float) $this->minPrice))
            ->when($this->maxPrice !== '', fn ($query) => $query->where('price', '<=', (float) $this->maxPrice))
            ->when($this->availability === 'disponivel', fn ($query) => $query->where('stock', '>', 0))
            ->when($this->availability === 'esgotado', fn ($query) => $query->where('stock', 0))
            ->when($this->minRating !== '', fn ($query) => $query->whereHas('reviews', fn ($query) => $query->where('rating', '>=', $minimumRating)))
            ->orderBy('name')->get();

        return view('livewire.product-catalog', ['products' => $products, 'site' => $site, 'categories' => $this->siteScoped(Category::query())->orderBy('name')->get()]);
    }
}
