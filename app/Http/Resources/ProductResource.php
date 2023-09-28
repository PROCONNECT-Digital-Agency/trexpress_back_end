<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var Product|JsonResource $this */

        return [
            'id' => (int)$this->id,
            'uuid' => (string)$this->uuid,
            'shop_id' => (int)$this->shop_id,
            'category_id' => (int)$this->category_id,
            'keywords' => (string)$this->keywords,
            'brand_id' => (int)$this->brand_id,
            'tax' => (int)$this->tax,
            'min_qty' => $this->min_qty,
            'max_qty' => $this->max_qty,
            'bar_code' => $this->bar_code,
            'active' => (boolean)$this->active,
            'status' => $this->status,
            'img' => $this->img ?? null,
            'created_at' => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            'updated_at' => $this->when($this->updated_at, optional($this->updated_at)->format('Y-m-d H:i:s')),

            'orders_count' => $this->when($this->orders_count, $this->orders_count),
            'rating_avg' => $this->reviews_avg_rating,
            'reviews_count' => $this->when($this->reviews_count, $this->reviews_count),
            'rating_percent' => $this->reviews_count ? $this->ratingPercent() : [],
            'review' => $this->reviewAble(),
            'reviews_counts' => $this->reviews->avg('rating'),

            // Relations
            'translation' => TranslationResource::make($this->whenLoaded('translation')),
            'translations' => TranslationResource::collection($this->whenLoaded('translations')),
            'properties' => ProductPropertyResource::collection($this->whenLoaded('properties')),
            'stocks' => StockResource::collection($this->whenLoaded('stocks')),
            'shop' => ShopResource::make($this->whenLoaded('shop')),
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'brand' => BrandResource::make($this->whenLoaded('brand')),
            'unit' => UnitResource::make($this->whenLoaded('unit')),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            'galleries' => GalleryResource::collection($this->whenLoaded('galleries')),
            'extras' => ExtraGroupResource::collection($this->whenLoaded('extras')),
        ];
    }

    public function ratingPercent()
    {
        $reviews = $this->reviews()->select('rating')->get();

        $reviews = collect($reviews)->mapToGroups(function ($item) use ($reviews) {
            $rating = $reviews->where('rating', $item->rating)->count();
            return [$item->rating => ($rating * 100) / $reviews->count()];
        });

        // Loop the collection through map and sum the amount of each group
        return $reviews->map(function ($item) {
            return $item->pipe(function ($value) {
                return collect($value)->unique()[0];
            });
        });
    }

    protected function reviewAble(): bool
    {
        if (auth('sanctum')->user() && request('review')) {
            $stockIds = Stock::where('countable_id',$this->id)->pluck('id');
            $orderProduct = Order::with([
                'orderDetails',
                'orderDetails.orderStocks'])
                ->whereHas('orderDetails.orderStocks', function ($query) use ($stockIds) {
                    $query->whereIn('stock_id', $stockIds);
                })
                ->where('status',Order::DELIVERED)
                ->where('user_id', auth('sanctum')->user()->id)
                ->first();
            if ($orderProduct){
                return true;
            }
            return false;
        }
        return false;
    }

}
