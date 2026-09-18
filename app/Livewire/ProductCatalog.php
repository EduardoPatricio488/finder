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
    public ?int $selectedSiteId = null;
    public string $category = '';
    public string $minPrice = '';
    public string $maxPrice = '';
    public string $availability = '';
    public string $minRating = '';
    public string $sortBy = 'name';
    public string $catalogFilter = '';
    public $importFile;
    public bool $productModalOpen = false;
    public bool $categoriesModalOpen = false;
    public string $newCategoryName = '';
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
    public array $productMediaIds = [];
    public bool $productsApplied = false;
    public array $cart = [];
    public string $couponCode = '';
    public ?string $appliedCouponCode = null;
    public float $couponDiscount = 0;
    public string $couponMessage = '';
    public bool $cartOpen = false;
    public bool $checkoutOpen = false;
    public bool $quickStockModalOpen = false;
    public bool $stockHistoryModalOpen = false;
    public ?int $stockProductId = null;
    public string $stockProductName = '';
    public int $stockChange = 0;
    public string $stockType = 'entrada';
    public string $stockReason = '';
    public array $selectedProducts = [];
    public bool $selectAllProducts = false;
    public string $customerName = '';
    public string $customerEmail = '';
    public string $customerPhone = '';
    public string $deliveryAddress = '';
    public string $paymentMethod = 'mbway';
    public ?string $completedOrderNumber = null;

    private function site(): Site
    {
        if ($this->selectedSiteId !== null) {
            $site = SiteContext::manageableSitesQuery(auth()->user())->find($this->selectedSiteId);
            abort_unless($site instanceof Site, 404);

            return $site;
        }

        return SiteContext::current();
    }
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
            if ($site !== null) return redirect()->route('products');
        }
        if (! auth()->check()) return null;

        $user = auth()->user();
        $availableSites = SiteContext::manageableSitesQuery($user)->orderBy('sort_order')->orderBy('name')->get(['id']);
        $sessionSiteId = session('current_site_id');
        $this->selectedSiteId = $availableSites->contains('id', $sessionSiteId)
            ? (int) $sessionSiteId
            : ($availableSites->first()?->id);

        abort_unless($this->selectedSiteId !== null, 404);

        session()->put('current_site_id', $this->selectedSiteId);

        $this->cart = session($this->cartKey(), []);
        $this->productsApplied = filled(data_get($this->site()->settings, 'products_applied_at'));
        $this->cartOpen = request()->query('cart') === '1';
        $this->customerName = $user->name;
        $this->customerEmail = $user->email;
        $this->customerPhone = (string) $this->site()->customers()->where('email', $user->email)->value('phone');
        return null;
    }

    public function updatedSelectedSiteId($siteId): void
    {
        $siteId = (int) $siteId;
        abort_unless(SiteContext::manageableSitesQuery(auth()->user())->whereKey($siteId)->exists(), 404);

        $this->selectedSiteId = $siteId;
        session()->put('current_site_id', $siteId);
        $this->productsApplied = filled(data_get(Site::find($siteId)?->settings, 'products_applied_at'));

        $this->reset([
            'search',
            'category',
            'minPrice',
            'maxPrice',
            'availability',
            'minRating',
            'catalogFilter',
            'selectedProducts',
        ]);
        $this->sortBy = 'name';
        $this->selectAllProducts = false;
        $this->closeProductModal();
        $this->closeManageProductModal();
        $this->closeQuickStockModal();
        $this->closeStockHistory();
        $this->closeImageGallery();

        $user = auth()->user();
        $this->customerPhone = (string) $this->site()->customers()->where('email', $user->email)->value('phone');
    }

    public function openCategoriesModal(): void
    {
        $this->reset('newCategoryName');
        $this->resetValidation('newCategoryName');
        $this->categoriesModalOpen = true;
    }

    public function closeCategoriesModal(): void
    {
        $this->categoriesModalOpen = false;
        $this->reset('newCategoryName');
        $this->resetValidation();
    }

    public function saveCategory(): void
    {
        $this->newCategoryName = trim($this->newCategoryName);
        $validated = $this->validate([
            'newCategoryName' => ['required', 'string', 'max:255'],
        ], [
            'newCategoryName.required' => 'O nome da categoria é obrigatório.',
        ]);

        $site = $this->site();
        $slug = Str::slug($validated['newCategoryName']);
        if ($slug === '') {
            $this->addError('newCategoryName', 'Introduza um nome de categoria válido.');
            return;
        }

        $baseSlug = $slug;
        $suffix = 2;
        while ($site->categories()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        $category = $site->categories()->create(['name' => $validated['newCategoryName'], 'slug' => $slug]);

        // A nova categoria fica imediatamente disponível e selecionada no formulário do produto.
        $this->productCategoryId = $category->id;
        $this->productCustomCategory = '';
        $this->reset('newCategoryName');
        $this->resetValidation('newCategoryName');
        $this->categoriesModalOpen = false;

        session()->flash('status', 'Categoria criada com sucesso.');
    }

    public function deleteCategory(int $categoryId): void
    {
        $category = $this->site()->categories()->withCount('products')->findOrFail($categoryId);
        if ($category->products_count > 0) {
            $this->addError('categoryDelete', 'Esta categoria tem produtos associados e não pode ser eliminada.');
            return;
        }

        $category->delete();
        session()->flash('status', 'Categoria eliminada com sucesso.');
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

    }

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'category',
            'minPrice',
            'maxPrice',
            'availability',
            'minRating',
            'catalogFilter',
        ]);

        $this->sortBy = 'name';
    }

    public function updatedSelectAllProducts(bool $value): void
    {
        $this->selectedProducts = $value
            ? $this->siteScoped(Product::query())->pluck('id')->map(fn ($id) => (string) $id)->all()
            : [];
    }

    public function updatedImportFile(): void
    {
        $this->importProducts();
    }

    public function importProducts(): void
    {
        $this->validate(['importFile' => ['required', 'file', 'mimes:csv,txt', 'max:5120']]);
        $handle = fopen($this->importFile->getRealPath(), 'r');
        $header = fgetcsv($handle, 0, ';') ?: [];
        $header = array_map(fn ($value) => Str::lower(trim((string) $value)), $header);
        $map = array_flip($header);
        $site = $this->site();
        $count = 0;
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $name = trim((string) ($row[$map['nome'] ?? -1] ?? ''));
            if ($name === '') continue;
            $categoryName = trim((string) ($row[$map['categoria'] ?? -1] ?? 'Outros')) ?: 'Outros';
            $category = $site->categories()->firstOrCreate(['slug' => Str::slug($categoryName)], ['name' => $categoryName]);
            $product = $site->products()->create([
                'category_id' => $category->id,
                'name' => $name,
                'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
                'price' => (float) str_replace(',', '.', (string) ($row[$map['preço'] ?? -1] ?? 0)),
                'stock' => (int) ($row[$map['stock'] ?? -1] ?? 0),
                'minimum_stock' => (int) ($row[$map['stock mínimo'] ?? -1] ?? 0),
                'description' => trim((string) ($row[$map['descrição'] ?? -1] ?? '')) ?: null,
                'is_active' => true,
            ]);
            $count++;
        }
        fclose($handle);
        $this->reset('importFile');
        session()->flash('status', $count.' produtos importados com sucesso.');
    }

    public function deleteProductImage(int $productId, int $index): void
    {
        $product = $this->siteScoped(Product::query())->findOrFail($productId);
        $images = array_values($product->images ?: ($product->image_url ? [$product->image_url] : []));
        if (!isset($images[$index])) return;
        Storage::disk('public')->delete($images[$index]);
        array_splice($images, $index, 1);
        $product->update(['images' => $images ?: null, 'image_url' => $images[0] ?? null]);
        $this->imageGalleryPhotos = $images;
        $this->imageGalleryIndex = 0;
    }

    public function setPrimaryImage(int $productId, int $index): void
    {
        $product = $this->siteScoped(Product::query())->findOrFail($productId);
        $images = array_values($product->images ?: ($product->image_url ? [$product->image_url] : []));
        if (!isset($images[$index])) return;
        $primary = $images[$index];
        unset($images[$index]);
        array_unshift($images, $primary);
        $product->update(['image_url' => $images[0], 'images' => array_values($images)]);
        $this->imageGalleryPhotos = array_values($images);
        $this->imageGalleryIndex = 0;
    }

    public function updatedSelectedProducts(): void
    {
        $this->selectAllProducts = false;
    }

    public function openQuickStockModal(int $productId): void
    {
        $product = $this->siteScoped(Product::query())->findOrFail($productId);
        $this->stockProductId = $product->id;
        $this->stockProductName = (string) $product->name;
        $this->stockChange = 0;
        $this->stockType = 'entrada';
        $this->stockReason = '';
        $this->resetValidation();
        $this->quickStockModalOpen = true;
    }

    public function closeQuickStockModal(): void
    {
        $this->quickStockModalOpen = false;
        $this->stockProductId = null;
        $this->stockProductName = '';
        $this->stockChange = 0;
        $this->stockType = 'entrada';
        $this->stockReason = '';
    }

    public function saveQuickStock(): void
    {
        $data = $this->validate([
            'stockChange' => ['required', 'integer', 'not_in:0'],
            'stockReason' => ['nullable', 'string', 'max:255'],
        ]);
        $product = $this->siteScoped(Product::query())->findOrFail($this->stockProductId);
        $quantity = abs((int) $data['stockChange']);
        $signedQuantity = $this->stockType === 'saida' ? -$quantity : $quantity;
        $newStock = $product->stock + $signedQuantity;
        abort_if($newStock < 0, 422, 'O stock não pode ficar negativo.');
        $product->update(['stock' => $newStock]);
        $product->stockMovements()->create([
            'site_id' => $this->site()->id,
            'user_id' => auth()->id(),
            'type' => $this->stockType,
            'quantity' => $signedQuantity,
            'note' => $data['stockReason'] ?: 'Alteração rápida de stock',
        ]);
        $this->closeQuickStockModal();
        session()->flash('status', 'Stock atualizado com sucesso.');
        app(FinderNotificationService::class)->productChanged($this->site(), 'O stock de "'.$product->name.'" foi alterado para '.$newStock.'.');
        if ($newStock > 0 && $newStock <= $product->minimum_stock) {
            app(FinderNotificationService::class)->lowStock($this->site(), 'O produto "'.$product->name.'" está com stock baixo ('.$newStock.' unidades).');
        }
    }

    public function openStockHistory(int $productId): void
    {
        $product = $this->siteScoped(Product::query())->findOrFail($productId);
        $this->stockProductId = $product->id;
        $this->stockProductName = (string) $product->name;
        $this->stockHistoryModalOpen = true;
    }

    public function closeStockHistory(): void
    {
        $this->stockHistoryModalOpen = false;
        $this->stockProductId = null;
        $this->stockProductName = '';
    }

    public function duplicateProduct(int $productId): void
    {
        $product = $this->siteScoped(Product::query())->findOrFail($productId);
        $copy = $product->replicate();
        $copy->name = $product->name.' (cópia)';
        $copy->slug = Str::slug($copy->name).'-'.Str::lower(Str::random(5));
        $copy->stock = 0;
        $copy->image_url = $product->image_url;
        $copy->images = $product->images;
        $copy->save();
        session()->flash('status', 'Produto duplicado com sucesso.');
    }

    public function deleteProduct(int $productId): void
    {
        $product = $this->siteScoped(Product::query())->findOrFail($productId);
        abort_if($product->orderItems()->exists(), 422, 'Este produto já tem vendas e não pode ser eliminado.');
        $product->delete();
        $this->productsApplied = false;
        session()->flash('status', 'Produto eliminado com sucesso.');
    }

    public function bulkDelete(): void
    {
        $ids = array_map('intval', $this->selectedProducts);
        if ($ids === []) return;
        $products = $this->siteScoped(Product::query())->whereIn('id', $ids)->get();
        foreach ($products as $product) {
            if (! $product->orderItems()->exists()) $product->delete();
        }
        $this->selectedProducts = [];
        $this->selectAllProducts = false;
        session()->flash('status', 'Produtos seleccionados processados.');
    }

    public function exportProducts(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $products = $this->siteScoped(Product::query())->with('category')->orderBy('name')->get();
        $userName = auth()->user()?->name ?: 'Utilizador';
        $filename = Str::slug($userName).'-produtos-'.now()->format('Y-m-d').'.csv';
        return response()->streamDownload(function () use ($products): void {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['Nome', 'Categoria', 'Preço', 'Stock', 'Stock mínimo', 'Descrição'], ';');
            foreach ($products as $product) {
                fputcsv($handle, [$product->name, $product->category?->name, $product->price, $product->stock, $product->minimum_stock, $product->description], ';');
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function applyProducts(): void
    {
        $settings = $this->site()->settings ?? [];
        data_set($settings, 'products_applied_at', now()->toIso8601String());
        $this->site()->update(['settings' => $settings]);
        $this->site()->refresh();
        $this->productsApplied = true;
        session()->flash('products-applied', 'Os produtos e respetivas imagens foram aplicados no site.');
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
        $this->stockProductId = $productId;
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
        $this->productMediaIds = [];
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
            'productMediaIds' => ['nullable', 'array', 'max:10'],
            'productMediaIds.*' => ['integer'],
            'productImages.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:10240']
        ], [
            'productImages.array' => 'As fotografias selecionadas são inválidas.',
            'productImages.max' => 'Podes adicionar no máximo 10 fotografias.',
            'productImages.*.file' => 'O ficheiro selecionado não é válido.',
            'productImages.*.mimes' => 'Cada fotografia tem de estar em JPG, JPEG, PNG ou WEBP.',
            'productImages.*.max' => 'Cada fotografia pode ter no máximo 10 MB.',
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

        $this->productsApplied = false;
        $this->closeManageProductModal();
        session()->flash('status', 'Produto atualizado com sucesso.');
        app(FinderNotificationService::class)->productChanged($site, 'Foi atualizado o produto "'.$product->name.'".');
        if ($product->stock > 0 && $product->stock <= $product->minimum_stock) {
            app(FinderNotificationService::class)->lowStock($site, 'O produto "'.$product->name.'" está com stock baixo ('.$product->stock.' unidades).');
        }
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
            'productImages.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:10240']
        ], [
            'productImages.array' => 'As fotografias selecionadas são inválidas.',
            'productImages.max' => 'Podes adicionar no máximo 10 fotografias.',
            'productImages.*.file' => 'O ficheiro selecionado não é válido.',
            'productImages.*.mimes' => 'Cada fotografia tem de estar em JPG, JPEG, PNG ou WEBP.',
            'productImages.*.max' => 'Cada fotografia pode ter no máximo 10 MB.',
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

        if ($this->productMediaIds !== []) {
            $media = $site->media()->whereIn('id', $this->productMediaIds)->where('placement', 'products')->get();
            $paths = $media->pluck('path')->values()->all();
            $product->update(['image_url' => $paths[0] ?? null, 'images' => $paths]);
        } elseif ($this->productImages !== []) {
            $paths = [];
            foreach ($this->productImages as $image) {
                $paths[] = $image->store('products', 'public');
            }
            $product->update([
                'image_url' => $paths[0] ?? null,
                'images' => $paths,
            ]);
        }

        $this->productsApplied = false;
        $this->productModalOpen = false;
        $this->resetProductForm();
        session()->flash('status', 'Produto adicionado com sucesso.');
        app(FinderNotificationService::class)->productChanged($site, 'Foi adicionado o produto "'.$product->name.'".');
        if ($product->stock > 0 && $product->stock <= $product->minimum_stock) {
            app(FinderNotificationService::class)->lowStock($site, 'O produto "'.$product->name.'" foi criado com stock baixo ('.$product->stock.' unidades).');
        }
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
            'productMediaIds',
            'productStock',
            'productMinimumStock',
        ]);
        $this->productIsActive = true;
        $this->productMediaIds = [];
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
        $site = $this->site();
        $availableSites = SiteContext::manageableSitesQuery(auth()->user())
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $minimumRating = (float) $this->minRating;
        $products = $this->siteScoped(Product::query())->where('is_active', true)->with('category')->with(['promotions' => fn ($query) => $query->where('starts_at', '<=', now())->where('ends_at', '>=', now())])->withAvg('reviews', 'rating')->withCount('reviews')
            ->when($this->search !== '', fn ($query) => $query->where(fn ($query) => $query->where('name', 'like', '%'.$this->search.'%')->orWhere('description', 'like', '%'.$this->search.'%')->orWhere('sku', 'like', '%'.$this->search.'%')->orWhere('price', 'like', '%'.$this->search.'%')->orWhere('tags', 'like', '%'.$this->search.'%')->orWhereHas('category', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))))
            ->when($this->category !== '', fn ($query) => $query->whereHas('category', fn ($query) => $query->where('slug', $this->category)))
            ->when($this->minPrice !== '', fn ($query) => $query->where('price', '>=', (float) $this->minPrice))
            ->when($this->maxPrice !== '', fn ($query) => $query->where('price', '<=', (float) $this->maxPrice))
            ->when($this->availability === 'disponivel', fn ($query) => $query->where('stock', '>', 0))
            ->when($this->availability === 'baixo', fn ($query) => $query->whereColumn('stock', '<=', 'minimum_stock')->where('stock', '>', 0))
            ->when($this->availability === 'esgotado', fn ($query) => $query->where('stock', 0))
            ->when($this->minRating !== '', fn ($query) => $query->whereHas('reviews', fn ($query) => $query->where('rating', '>=', $minimumRating)))
            ->when($this->catalogFilter === 'sem_imagem', fn ($query) => $query->whereNull('image_url'))
            ->when($this->catalogFilter === 'sem_descricao', fn ($query) => $query->where(function ($q) { $q->whereNull('description')->orWhere('description', ''); }))
            ->when($this->sortBy === 'price_asc', fn ($query) => $query->orderBy('price'))
            ->when($this->sortBy === 'price_desc', fn ($query) => $query->orderByDesc('price'))
            ->when($this->sortBy === 'stock', fn ($query) => $query->orderByDesc('stock'))
            ->when($this->sortBy === 'rating', fn ($query) => $query->orderByDesc('reviews_avg_rating'))
            ->when($this->sortBy === 'newest', fn ($query) => $query->latest())
            ->when($this->sortBy === 'name', fn ($query) => $query->orderBy('name'))->get();

        return view('livewire.product-catalog', [
            'products' => $products,
            'stockValue' => (float) $this->siteScoped(Product::query())
                ->where('is_active', true)->sum(DB::raw('price * stock')),
            'lowStockProducts' => $products->filter(fn (Product $product): bool => $product->stock > 0 && $product->stock <= $product->minimum_stock),
            'site' => $site,
            'availableSites' => $availableSites,
            'productMedia' => $site->media()->where('placement', 'products')->latest()->get(),
            'categories' => $this->siteScoped(Category::query())->withCount('products')->orderBy('name')->get(),
            'stockHistory' => $this->stockProductId ? $this->siteScoped(Product::query())->find($this->stockProductId)?->stockMovements()->with('user')->latest()->limit(30)->get() : collect(),
        ]);
    }
}
