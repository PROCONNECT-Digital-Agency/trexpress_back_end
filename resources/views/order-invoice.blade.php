<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Invoice</title>
</head>
<style>
    .ant-card {
        box-sizing: border-box;
        padding: 0;
        color: #455560;
        font-size: 14px;
        font-variant: tabular-nums;
        line-height: 1.5;
        list-style: none;
        -webkit-font-feature-settings: 'tnum';
        font-feature-settings: 'tnum';
        position: relative;
        background: #fff;
        border-radius: 0.625rem;
    }

    .ant-card-body {
        padding: 20px;
    }

    .ant-card-body::before {
        display: table;
        content: '';
    }

    .ant-card-body::after {
        display: table;
        display: block;
        clear: both;
        content: '';
    }

    .ant-card-bordered,
    .api-container table {
        border: 1px solid #e6ebf1;
    }

    .d-flex {
        display: flex !important;
    }

    .justify-content-between {
        justify-content: space-between !important;
    }

    .mt-3,
    .my-3 {
        margin-top: 1rem !important;
    }

    .mb-1,
    .my-1 {
        margin-bottom: 0.25rem !important;
    }

    .font-weight-semibold {
        font-weight: 500 !important;
    }

    address {
        margin-bottom: 1em;
        font-style: normal;
        line-height: inherit;
    }

    .text-dark {
        color: #1a3353 !important;
    }

    .font-size-md {
        font-size: 17px !important;
    }

    .mt-4,
    .my-4 {
        margin-top: 1.5rem !important;
    }

    .ant-table-wrapper {
        clear: both;
        max-width: 100%;
    }

    .ant-table-wrapper::before {
        display: table;
        content: '';
    }

    .ant-table-wrapper::after {
        display: table;
        display: block;
        clear: both;
        content: '';
    }

    .ant-table {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        color: #455560;
        font-variant: tabular-nums;
        line-height: 1.5;
        list-style: none;
        -webkit-font-feature-settings: 'tnum';
        font-feature-settings: 'tnum';
        position: relative;
        font-size: 14px;
        background: #fff;
        border-radius: 0.625rem;
    }

    .ant-table table {
        width: 100%;
        text-align: left;
        border-radius: 0.625rem 0.625rem 0 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .ant-table-thead > tr > th {
        color: #1a3353;
        text-align: left;
        background: #fff;
        border-bottom: 1px solid #e6ebf1;
        transition: background 0.3s ease;
    }

    .ant-table tfoot > tr > td,
    .ant-table tfoot > tr > th,
    .ant-table-tbody > tr > td,
    .ant-table-thead > tr > th {
        position: relative;
        padding: 10px;
        overflow-wrap: break-word;
    }

    .ant-table-cell-ellipsis {
        white-space: nowrap;
    }

    .ant-table-cell-ellipsis.ant-table-cell-fix-left-last,
    .ant-table-cell-ellipsis.ant-table-cell-fix-right-first {
        overflow: visible;
    }

    .ant-table-cell-ellipsis.ant-table-cell-fix-left-last
    .ant-table-cell-content,
    .ant-table-cell-ellipsis.ant-table-cell-fix-right-first
    .ant-table-cell-content {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .ant-table-cell-ellipsis,
    .ant-table-cell-ellipsis .ant-table-column-title {
        overflow: hidden;
        text-overflow: ellipsis;
        word-break: keep-all;
    }

    .ant-table-title {
        padding: 16px;
    }

    .ant-table-footer {
        padding: 16px;
        color: #1a3353;
        background: #fafafa;
    }

    .ant-table-thead > tr > th[colspan]:not([colspan='1']),
    .ant-table-wrapper-rtl
    .ant-table-thead
    > tr
    > th[colspan]:not([colspan='1']) {
        text-align: center;
    }

    .ant-table-thead
    > tr
    > th:not(:last-child):not(.ant-table-selection-column):not(.ant-table-row-expand-icon-cell):not([colspan])::before {
        position: absolute;
        top: 50%;
        right: 0;
        width: 1px;
        height: 1.6em;
        background-color: rgba(0, 0, 0, 0.06);
        -webkit-transform: translateY(-50%);
        transform: translateY(-50%);
        transition: background-color 0.3s;
        content: '';
    }

    .ant-table-thead > tr:not(:last-child) > th[colspan] {
        border-bottom: 0;
    }

    .ant-table-tbody > tr > td {
        border-bottom: 1px solid #e6ebf1;
        transition: background 0.3s;
    }

    .ant-table-tbody
    > tr
    > td
    > .ant-table-expanded-row-fixed
    > .ant-table-wrapper:only-child
    .ant-table,
    .ant-table-tbody > tr > td > .ant-table-wrapper:only-child .ant-table {
        margin: -16px -16px -16px 33px;
    }

    .ant-table-tbody
    > tr
    > td
    > .ant-table-expanded-row-fixed
    > .ant-table-wrapper:only-child
    .ant-table-tbody
    > tr:last-child
    > td,
    .ant-table-tbody
    > tr
    > td
    > .ant-table-wrapper:only-child
    .ant-table-tbody
    > tr:last-child
    > td {
        border-bottom: 0;
    }

    .ant-table-tbody
    > tr
    > td
    > .ant-table-expanded-row-fixed
    > .ant-table-wrapper:only-child
    .ant-table-tbody
    > tr:last-child
    > td:first-child,
    .ant-table-tbody
    > tr
    > td
    > .ant-table-expanded-row-fixed
    > .ant-table-wrapper:only-child
    .ant-table-tbody
    > tr:last-child
    > td:last-child,
    .ant-table-tbody
    > tr
    > td
    > .ant-table-wrapper:only-child
    .ant-table-tbody
    > tr:last-child
    > td:first-child,
    .ant-table-tbody
    > tr
    > td
    > .ant-table-wrapper:only-child
    .ant-table-tbody
    > tr:last-child
    > td:last-child {
        border-radius: 0;
    }

    .ant-table-tbody > tr.ant-table-row:hover > td,
    .ant-table-tbody > tr > td.ant-table-cell-row-hover,
    td.ant-table-column-sort {
        background: #fafafa;
    }

    .ant-table-tbody > tr.ant-table-row-selected > td {
        background: #f0f7ff;
        border-color: rgba(0, 0, 0, 0.03);
    }

    .ant-table-tbody > tr.ant-table-row-selected:hover > td {
        background: #e6f2ff;
    }

    .justify-content-end {
        justify-content: flex-end !important;
    }

    .text-right {
        text-align: right !important;
    }

    .text-center {
        text-align: center !important;
    }

    .border-bottom {
        border-bottom: 1px solid #e6ebf1 !important;
    }

    .mb-2,
    .my-2 {
        margin-bottom: 0.5rem !important;
    }

    .mx-1 {
        margin-right: 0.25rem !important;
    }

    body {
        font-family: 'Inter', sans-serif !important;
    }
</style>
<body>
<div class="ant-card ant-card-bordered">
    <div class="ant-card-body">
        <div class="d-flex justify-content-between mt-3">
            @foreach($order->orderDetails as $detail)
                <div class="float-right ml-10" style="display: inline-block; margin-left: 20px">
                    <h2 class="mb-1 font-weight-semibold">Shop Name: {{ $detail->shop->translation->title }}</h2>
                    <p><img class="ml-1" style="width:100px;height:100px;"
                            src="https://mupza-space.ams3.digitaloceanspaces.com/public/images/{{$detail->shop->logo_img}}">
                    </p>
                </div>
            @endforeach
            <div>
                <h2 class="mb-1 font-weight-semibold">Invoice #{{ $order->id }}</h2>
                <p>{{ $order->created_at->format('d/m/Y') }}</p>
            </div>
            <address>
                <p>
                    <span
                        class="font-weight-semibold text-dark font-size-md"> {{ $order->user->firstname }} {{ $order->user->lastname }}</span><br/>
                    <span>Phone: {{ $order->user->phone ?? '' }}</span><br/>
                    <span>Email: {{ $order->user->email ?? '' }}</span><br/>
                    <span>Delivery Type: {{ $order?->delivery?->translation?->title }}</span><br/>
                    {{--                  <span>Delivery address: {{ count($order->orderDetails) ? $order->orderDetails[0]->deliveryAddress->address : '' }}</span>--}}
                </p>
            </address>
        </div>
        <div class="mt-4">
            <div class="ant-table-wrapper mb-5">
                <div class="ant-spin-nested-loading">
                    <div class="ant-spin-container">
                        <div class="ant-table">
                            <div class="ant-table-container">
                                <div class="ant-table-content">
                                    <table>
                                        <thead class="ant-table-thead">
                                        <tr>
                                            <th class="ant-table-cell">No.</th>
                                            <th style="width: 70%" class="ant-table-cell">Product</th>
                                            <th style="width: 70%" class="ant-table-cell">Shop</th>
                                            <th class="ant-table-cell">Quantity</th>
                                            <th class="ant-table-cell">Discount</th>
                                            <th  style="width: 70%" class="ant-table-cell">Bar Code</th>
                                            <th class="ant-table-cell">Price</th>
                                        </tr>
                                        </thead>
                                        <tbody class="ant-table-tbody">
                                        @foreach($order->orderDetails as $detail)
                                            @foreach($detail->orderStocks as $product)
                                                <tr data-row-key="436" class="ant-table-row ant-table-row-level-0">
                                                    <td class="ant-table-cell">{{ $product->id }}</td>
                                                    <td class="ant-table-cell">
                                                        <p style="width: 70%">
                                                            {{ $product->stock->countable->translation->title }}
                                                        </p>
                                                    </td>
                                                    <td class="ant-table-cell">
                                                        <p style="width: 70%">{{ $detail->shop->translation->title }}</p>
                                                    </td>
                                                    <td class="ant-table-cell">{{ $product->quantity }}</td>
                                                    <td class="ant-table-cell">{{ $product->discount }}</td>
                                                    <td class="ant-table-cell">
                                                        <p style="width: 70%">{{ $product->stock->countable->bar_code }}</p>
                                                    </td>
                                                    <td class="ant-table-cell">
                                                        <p>{{ round($product->origin_price * $order->currency->rate,2) }}</p>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <div class="text-right">
                    <div class="border-bottom">
                        <p>Currency: {{$order->currency->short_code}}</p>
                        <p class="mb-2"><span>Sub total amount: </span>
                            {{ round(($order->price - $order->total_delivery_fee) * $order->currency->rate, 2) }}
                        </p>
                        <p>Delivery price :{{ round($order->total_delivery_fee * $order->currency->rate, 2) }}</p>
                        <p>Tax :{{ round($order->orderDetails->sum('tax') * $order->currency->rate, 2) }}</p>
                        {{--                        <p>Coupon : {{ $order->currency->symbol }} {{ round($order->orderDetails->sum('tax') * $order->currency->rate, 2) }}</p>--}}
                    </div>
                    <h2 class="font-weight-semibold mt-3">
                        <span class="mr-1">Grand total: </span>{{ round($order->price * $order->currency->rate, 2) }}
                    </h2>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
