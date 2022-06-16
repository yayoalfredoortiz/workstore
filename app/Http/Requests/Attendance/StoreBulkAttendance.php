<?php

namespace App\Http\Requests\Attendance;

use App\Http\Requests\CoreRequest;

class StoreBulkAttendance extends CoreRequest
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
            'clock_in_time' => 'required',
            'clock_out_time' => 'required',
            'working_from' => 'required',
            'year' => 'required',
            'month' => 'required',
            'user_id.0' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'user_id.0.required' => __('messages.atleastOneValidation')
        ];
    }

}
