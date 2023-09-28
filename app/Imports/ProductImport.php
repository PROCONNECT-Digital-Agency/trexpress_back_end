<?php

namespace App\Imports;

ini_set('memory_limit', '4000M');
set_time_limit(0);

use App\Models\Brand;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Discount;
use App\Models\ExtraGroup;
use App\Models\ExtraValue;
use App\Models\Gallery;
use App\Models\Product;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;

class ProductImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation, SkipsOnFailure, ShouldQueue
{
    use Importable, SkipsFailures,Queueable;

    public function __construct(private $shop_id)
    {

    }



    /**
     * @param Collection $collection
     * @return void
     */
    public function collection(Collection $collection): void
    {

        $lang = 'en';

        $notIn  = [];
        try {

            foreach ($collection as $row) {

                try {

                    DB::transaction(function () use ($row,$lang, &$notIn) {

                        $childCategory = $this->category($row,$lang);

                        $brand = null;

                        if (isset($row['brand_name'])) {

                            $brand = Brand::query()->where('title',$row['brand_name'])->first();

                            if (!$brand){
                                $brand = Brand::create([
                                    'title' => $row['brand_name'],
                                    'active' => 1,
                                ]);
                            }
                        }

                        $product = Product::withTrashed()
                            ->where('bar_code', $row['barcode'])->where('shop_id', $this->shop_id)
                            ->first();
                        if (!$product) {

                            $product = Product::create([
                                'category_id' => $childCategory,
                                'bar_code' => $row['barcode'] ?? null,
                                'shop_id' => $this->shop_id,
                                'brand_id' => $brand?->id ?? 6,
                                'min_qty' => 1,
                                'max_qty' => 100,
                                'tax' => 0,
                                'active' => 1
                            ]);

                            if (isset($row['product_name'])){
                                $product->translation()->create([
                                    'locale' => $lang,
                                    'title' => $row['product_name'],
                                    'description' => $row['description'] ?? null
                                ]);
                            }
                            if (isset($row['picture'])) {

                                $product->galleries()->delete();

                                $images = explode('https', $row['picture']);


                                foreach ($images as $image) {

                                    if (empty($image)) {
                                        continue;
                                    }

                                    try {
                                        $imageRemoveCharacter = preg_replace('/([\r\n\t])/', '', $image);

                                        $contents = file_get_contents('https' . $imageRemoveCharacter,'test');

                                        $randomStr = Str::random(6);

                                        $name = 'products/' . Str::slug(Carbon::now()->format('Y-m-d h:i:s')) . $randomStr . '.' . substr(strrchr($imageRemoveCharacter, "."), 1);

                                        Storage::put('public/images/' . $name, $contents);

                                        Gallery::create([
                                            'title' => Str::of($name)->after('/'),
                                            'path' => config('app.img_host').$name,
                                            'type' => 'products',
                                            'loadable_type' => 'App\Models\Product',
                                            'loadable_id' => $product->id,
                                        ]);


                                    } catch (\Throwable $e) {
                                        Log::error('failed img upload', [
                                            'url' => 'https' . $imageRemoveCharacter,
                                            'message' => $e->getMessage(),
                                        ]);
                                    }
                                }

                                $product->update(['img' => data_get($product->galleries->first(), 'path')]);
                            }

                        }

                        if(!empty($product->deleted_at)) {
                            $product->update([
                                'deleted_at' => null
                            ]);
                        }

                        $notIn[] = $product->id;

                        $quantity = $row['in_stock'] ?? 100;

                        $rate = 1;

                        if (isset($row['currency'])) {
                            $rate = max(Currency::where('short_code', $row['currency'])->first()?->rate, 1);
                        }

                        $price = 0;

                        if (isset($row['price'])) {
                            $price = round((double)str_replace(',', '.', $row['price']) / $rate, 2);
                        }

                        if (isset($row['old_price'])) {

                            $oldPrice = round((double)str_replace(',', '.', $row['old_price']) / $rate, 2);

                            if ($oldPrice > $price) {

                                $fixPrice = $oldPrice - $price;

                                $price = $oldPrice;

                                $discount = Discount::where('shop_id', $this->shop_id)->where('price', $fixPrice)
                                    ->where('type', 'fix')->first();

                                if (!$discount) {

                                    $discount = Discount::create([
                                        'shop_id' => $this->shop_id,
                                        'type' => 'fix',
                                        'price' => $fixPrice,
                                        'active' => 1,
                                        'start' => now(),
                                        'end' => now()->addYears(1)
                                    ]);

                                }

                                $product->discount()->attach(['discount_id' => $discount->id]);
                            }

                        }

                        $extraGroupSize = ExtraGroup::whereHas('translations', fn($q) => $q->where('locale', $lang)
                            ->where('title', 'Size'))->first();

                        if (!$extraGroupSize) {

                            $extraGroupSize = ExtraGroup::create([
                                'type' => 'text',
                                'active' => 1
                            ]);

                            $extraGroupSize->translation()->create([
                                'locale' => $lang,
                                'title' => 'Size',
                            ]);
                        }

                        $extraGroupColor = ExtraGroup::whereHas('translations', fn($q) => $q->where('locale', $lang)
                            ->where('title', 'Color'))->first();

                        if (!$extraGroupColor) {

                            $extraGroupColor = ExtraGroup::create([
                                'type' => 'color',
                                'active' => 1
                            ]);

                            $extraGroupColor->translation()->create([
                                'locale' => $lang,
                                'title' => 'Color',
                            ]);

                        }

                        $product->extras()->detach();

                        if (isset($row['size'])) {

                            $product->extras()->attach(['extra_group_id' => $extraGroupSize->id]);
                        }

                        if (isset($row['color'])) {
                            $product->extras()->attach(['extra_group_id' => $extraGroupColor->id]);
                        }

                        if ($product->stocks) {
                            $product->stocks()->delete();
                        }

                        if (!isset($row['size']) && !isset($row['color'])) {
                            Stock::query()->create([
                                'countable_id' => $product->id,
                                'countable_type' => 'App\Models\Product',
                                'price' => $price,
                                'quantity' => $quantity,
                            ]);
                        }

                        $params = [
                            'extraGroupColorId' => $extraGroupColor->id,
                            'price' => $price,
                            'quantity' => $quantity,
                            'productId' => $product->id,
                        ];

                        if (isset($row['size'])) {

                            $sizeArray = explode("\n", $row['size']);
                            foreach ($sizeArray as $size) {

                                $extraValueSize = ExtraValue::where('value', $size)->where('extra_group_id', $extraGroupSize->id)->first();

                                if (!$extraValueSize && ($size !== null)) {

                                    $extraValueSize = ExtraValue::create([
                                        'extra_group_id' => $extraGroupSize->id,
                                        'value' => $size,
                                        'active' => 1
                                    ]);

                                }

                                if (isset($row['color'])) {
                                    $params['value'] = $row['color'];

                                    $params['extraValueSizeId'] = $extraValueSize->id;

                                    $this->addExtraValueColor($params);

                                } else {

                                    $stock = Stock::query()->create([
                                        'countable_id' => $product->id,
                                        'countable_type' => 'App\Models\Product',
                                        'price' => $price,
                                        'quantity' => $quantity,
                                    ]);

                                    $stock->stockExtras()->attach(['extra_value_id' => $extraValueSize->id]);
                                }

                            }

                        } else if (isset($row['color'])) {

                            $params['value'] = $row['color'];

                            $this->addExtraValueColor($params);
                        }

                    });
                } catch (Throwable $e) {
                    Log::error('import row catch', [
                        $e->getMessage(),
                        $e->getCode(),
                        $e->getFile(),
                        $e->getTrace()
                    ]);
                }

            }

//            $stocks = Stock::query()->whereIn('countable_id', $notIn)->get();
//
//            foreach ($stocks as $stock) {
//                $stock->update([
//                    'quantity' => 0
//                ]);
//            }

        } catch (Throwable $e) {
            Log::error('import foreach catch', [
                $e->getMessage(),
                $e->getCode(),
                $e->getFile(),
                $e->getTrace()
            ]);
        }

    }


    private function addExtraValueColor(array $params)
    {
        $colorArray = explode("\n", $params['value']);

        foreach ($colorArray as $color) {

            $color = data_get(collect(config('colors'))->where('name', $color)->first(), 'key', $color);

            $extraValueColor = ExtraValue::where('value', $color)->where('extra_group_id', $params['extraGroupColorId'])->first();

            if (!$extraValueColor && ($color !== null)) {

                $extraValueColor = ExtraValue::create([
                    'extra_group_id' => $params['extraGroupColorId'],
                    'value' => $color,
                    'active' => 1
                ]);

            }

            $stock = Stock::query()->create([
                'countable_id' => $params['productId'],
                'countable_type' => 'App\Models\Product',
                'price' => $params['price'],
                'quantity' => $params['quantity'],
            ]);


            $stock->stockExtras()->attach(['extra_value_id' => $extraValueColor->id]);

            if (isset($params['extraValueSizeId'])) {
                $stock->stockExtras()->attach(['extra_value_id' => $params['extraValueSizeId']]);
            }

        }
    }

    private function category($row, string $lang)
    {
        $parentCategory = Category::query()
            ->whereHas('translations', fn($q) => $q->where('locale', $lang)
                ->where('title', $row['parent_category']))
            ->where('parent_id', 0)
            ->first();

        if (!$parentCategory) {

            $parentCategory = Category::query()->create([
                'parent_id' => 0
            ]);

            $parentCategory->translation()->create([
                'locale' => $lang,
                'title' => $row['parent_category']
            ]);

        }

        $category = Category::query()
            ->whereHas('translations', fn($q) => $q->where('locale', $lang)->where('title', $row['category']))
            ->where('parent_id', $parentCategory->id)
            ->first();

        if (!$category) {

            $category = Category::query()->create([
                'parent_id' => $parentCategory->id
            ]);

            $category->translation()->create([
                'locale' => $lang,
                'title' => $row['category']
            ]);
        }

        $childCategory = Category::query()
            ->whereHas('translations', fn($q) => $q->where('locale', $lang)
                ->where('title', $row['child_category']))
            ->where('parent_id', $category->id)
            ->first();

        if (!$childCategory) {

            $childCategory = Category::query()->create([
                'parent_id' => $category->id,
            ]);

            $childCategory->translation()->create([
                'locale' => $lang,
                'title' => $row['child_category']

            ]);
        }
        return $childCategory?->id;
    }

    public function rules(): array
    {
        return [
            'parent_category' => ['required'],
            'category' => ['required'],
            'child_category' => ['required'],
            'product_name' => ['required'],
        ];
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
