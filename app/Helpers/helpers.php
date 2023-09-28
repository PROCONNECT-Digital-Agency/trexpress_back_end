<?php

use App\Models\Category;
use App\Models\Currency;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

if (!function_exists('moneyFormatter')) {
    function moneyFormatter($number): string
    {
        [$whole, $decimal] = sscanf($number, '%d.%d');
        $money = number_format($whole, 0, ',', ' ');

        return $decimal ? $money . "," . substr($decimal, 0, 2) : $money;
    }
}

if (!function_exists('hasPermission')) {
    /**
     * Determine if the model may perform the given permission.
     *
     * @param string|int|Permission $permission
     * @param null $user
     * @param string|null $guardName
     *
     * @return bool
     */
    function hasPermission($permission, $user = null, string $guardName = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return false;
        }
        if (isAdmin($user)) {
            return true;
        }

        return $user->hasPermissionTo($permission, $guardName);
    }
}

if (!function_exists('hasRole')) {
    /**
     * Determine if the model has (one of) the given role(s).
     *
     * @param null $role
     * @param string|null $guard
     * @param null $user
     *
     * @return bool
     */
    function hasRole($role = null, $user = null, string $guard = null): bool
    {
        $user = $user ?? auth()->user();

        return $user && $user->hasRole($role, $guard);
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin($user = null): bool
    {
        $user = $user ?? auth()->user();

        return $user && $user->hasRole('admin');
    }
}

if (!function_exists('isEloquentModel')) {
    function isEloquentModel($query): bool
    {
        return isEloquent($query) and $query->getModel() instanceof \Illuminate\Database\Eloquent\Model;
    }
}

if (!function_exists('isEloquent')) {
    function isEloquent($query): bool
    {
        return $query instanceof EloquentBuilder;
    }
}

if (!function_exists('like')) {
    function like(): string
    {
        return DB::connection()->getDriverName() === 'postgresql' ? 'ilike' : 'like';
    }
}

if (!function_exists('dateFormat')) {
    function dateFormat(): string
    {
        return DB::connection()->getDriverName() === 'postgresql' ? 'to_char' : 'DATE_FORMAT';
    }
}

if (!function_exists('categoryParents')) {
    function categoryParents(Category $category, $text): string
    {
        $category->loadMissing('parenRecursive');
        if ($category->parenRecursive) {

            $text = categoryParents($category->parenRecursive, $text);

            $text .= " > " . $category->parenRecursive->translation_title;
        }

        return $text;
    }
}

if (!function_exists('dateFromToFormatter')) {
    function dateFromToFormatter(): array
    {
        $cacheEnabled = now()->diffInDays($date_to = Carbon::parse(request('date_to'))->addDay()) > 0 ? 86400 : 3600;//day or hour

        return [Carbon::parse(request('date_from')), $date_to, $cacheEnabled];
    }
}

if (!function_exists('orderSelectDateFormat')) {
    function orderSelectDateFormat($byTime)
    {
        return DB::raw("(DATE_FORMAT(created_at, " . ($byTime == 'year' ? "'%Y" : ($byTime == 'month' ? "'%Y-%m" : "'%Y-%m-%d")) . "')) as time");
    }
}

if (!function_exists('orderProductsTitle')) {
    function orderProductsTitle(\App\Models\Order $order)
    {
        $order->loadMissing('orderDetails.products.stock.countable.translation');
        $names = '';
        foreach ($order->orderDetails as $detail) {
            foreach ($detail->products as $product) {
                $names .= ", " . $product->stock->countable->translate;
            }
        }

        return trim($names, ', ');
    }
}

if (!function_exists('defaultCurrency')) {
    function defaultCurrency(): Currency
    {
        return Currency::where('active', 1)->where('default', 1)->first(['id', 'symbol', 'title', 'rate']);
    }
}
