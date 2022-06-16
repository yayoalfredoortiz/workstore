<?php

namespace App\Http\Requests\Admin\Contract;

use App\Http\Requests\CoreRequest;

class RenewRequest extends CoreRequest
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
        $setting = global_setting();
        return [
            'amount' => 'required',
            'start_date' => 'required|date_format:"' . $setting->date_format . '"',
            'end_date' => 'required|date_format:"' . $setting->date_format . '"',
        ];
    }

    public function messages()
    {
        return [
            'amount.required' => 'The amount field is required.',
            'start_date.required' => 'The start date field is required.',
            'end_date.required' => 'The end date field is required.'
        ];
    }

}
