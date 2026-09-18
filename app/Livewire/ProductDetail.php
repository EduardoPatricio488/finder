<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Support\SiteContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.catalog')]
class ProductDetail extends Component
{
    public Product $product;

    public int $rating = 5;

    public string $comment = '';

    public bool $canReview = false;

    public function mount(Product $product): void
    {
        $routeSite = request()->route('site');
        $site = $routeSite instanceof \App\Models\Site
            ? $routeSite
            : $product->site;

        abort_unless($site instanceof \App\Models\Site, 404);

        $preview = request()->boolean('preview');
        if ($preview) {
            abort_unless($site->isManageableBy(auth()->user()), 403);
        } else {
            abort_unless($site->status === 'online' && $site->is_published, 404);
        }

        abort_unless((int) $product->site_id === $site->id, 404);

        $this->product = $product;
        $this->canReview = auth()->check() && $this->purchaseQuery()->exists();
    }

    public function submitReview(): void
    {
        $this->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        abort_unless(auth()->check() && $this->canReview, 403);
        $order = $this->purchaseQuery()->latest('orders.created_at')->firstOrFail();

        $reviewData = ['order_id' => $order->id, 'rating' => $this->rating, 'comment' => $this->comment];

        if (Schema::hasColumn('product_reviews', 'site_id')) {
            $reviewData['site_id'] = SiteContext::storefront()->id;
        }

        ProductReview::query()->updateOrCreate(
            ['product_id' => $this->product->id, 'user_id' => auth()->id()],
            $reviewData,
        );

        $this->reset('comment');
        $this->rating = 5;
        session()->flash('review-status', 'A sua avaliação foi publicada.');
    }

    private function purchaseQuery(): Builder
    {
        $site = $this->product->site ?? SiteContext::storefront();

        return Order::query()
            ->when(Schema::hasColumn('orders', 'site_id'), fn ($query) => $query->where(function ($query) use ($site): void {
                $query->where('site_id', $site->id); 
            }))
            ->whereHas('customer', fn ($query) => $query->where('email', auth()->user()?->email))
            ->whereHas('items', fn ($query) => $query->where('product_id', $this->product->id))
            ->where('status', '!=', 'cancelada');
    }

    public function render(): mixed
    {
        $reviews = $this->product->reviews()->with('user')->latest()->get();

        return view('livewire.product-detail', [
            'reviews' => $reviews,
            'averageRating' => round((float) $reviews->avg('rating'), 1),
        ]);
    }
}
