<?php

namespace App\Http\Requests\User\Notification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserNotificationsRequest extends FormRequest
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
            'notifications'                     => 'required|array',
            'notifications.*.notification_id'   => ['required', 'int', Rule::exists('notifications', 'id')],
            'notifications.*.active'            => 'required|boolean',
        ];
    }
}
