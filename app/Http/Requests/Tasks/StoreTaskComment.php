<?php

namespace App\Http\Requests\Tasks;

use App\Http\Requests\CoreRequest;

class StoreTaskComment extends CoreRequest
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
            'comment' => [
                'required',
                function ($attribute, $value, $fail) {
                    $commnet = trim(str_replace('<p><br></p>', '', $value));

                    if ($commnet == '') {
                        $fail(__('validation.required'));
                    }
                }
            ]
        ];
    }

}
