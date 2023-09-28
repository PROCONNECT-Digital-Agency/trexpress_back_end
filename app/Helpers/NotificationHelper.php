<?php

namespace App\Helpers;

use App\Models\Language;
use App\Models\Notification;
use App\Models\Order;
use App\Models\ParcelOrder;
use App\Models\PushNotification;
use App\Models\Settings;
use App\Models\Translation;

class NotificationHelper
{
    use \App\Traits\Notification;
    public function deliveryManOrder(Order $order, ?string $language = null, string $type = 'deliveryman'): array
    {
        $km = (new Utility)->getDistance(
            optional($order->shop)->location,
            $order?->deliveryAddress?->location,
        );

        $second = Settings::adminSettings()->where('key', 'deliveryman_order_acceptance_time')->first();

        $order = Order::with('deliveryAddress')->find($order->id);

        return [
            'second' => data_get($second, 'value', 30),
            'order'  => $order->setAttribute('km', $km)->setAttribute('type', $type),
        ];
    }

    public function deliveryManParcelOrder(ParcelOrder $parcelOrder, string $type = 'deliveryman'): array
    {
        $km = (new Utility)->getDistance(
            $parcelOrder->address_from,
            $parcelOrder->address_to,
        );

        $second = Settings::adminSettings()->where('key', 'deliveryman_order_acceptance_time')->first();

        return [
            'second' => data_get($second, 'value', 30),
            'order'  => $parcelOrder->setAttribute('km', $km)->setAttribute('type', $type),
        ];
    }

    public function autoAcceptNotification(Order $order, string $lang, string $status): void
    {
        /** @var Notification $notification */
        $notification = $order->user?->notifications
            ?->where('type', Notification::ORDER_STATUSES)
            ?->first();

        if (in_array($order->status, ($notification?->payload ?? []))) {
            $userToken = $order->user?->firebase_token;
        }

        $firebaseTokens = array_merge(
            !empty($userToken) && is_array($userToken) ? $userToken : [],
        );

        $userIds = array_merge(
            !empty($userToken) && $order->user?->id ? [$order->user?->id] : [],
        );

        $default = data_get(Language::languagesList()->where('default', 1)->first(), 'locale');

        $tStatus = Translation::where(function ($q) use ($default, $lang) {
            $q->where('locale', $lang)->orWhere('locale', $default);
        })
            ->where('key', $status)
            ->first()?->value;

        $this->sendNotification(
            array_values(array_unique($firebaseTokens)),
            __('errors.' . ResponseError::NO_ERROR, ['status' => !empty($tStatus) ? $tStatus : $status], $lang),
            $order->id,
            [
                'id'     => $order->id,
                'status' => $order->status,
                'type'   => PushNotification::STATUS_CHANGED
            ],
            $userIds,
            __('errors.' . ResponseError::NO_ERROR, ['status' => !empty($tStatus) ? $tStatus : $status], $lang),
        );

    }
}

