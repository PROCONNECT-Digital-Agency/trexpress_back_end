<?php

namespace App\Http\Requests\Admin\Notification;

use App\Models\Notification;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type'      => ['required', 'string', Rule::in(Notification::TYPES)],
            'payload'   => 'array',
            'payload.*' => [
                request('type') === Notification::ORDER_STATUSES ? Rule::in(Order::STATUS) : 'string'
            ],
        ];
    }
}
