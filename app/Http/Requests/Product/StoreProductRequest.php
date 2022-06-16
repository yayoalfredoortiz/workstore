<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\CoreRequest;

class StoreProductRequest extends CoreRequest
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
            'name' => 'required',
            'price' => 'required|numeric',
            'downloadable_file' => 'required_if:downloadable,true|file',
        ];
    }

    public function messages()
    {
        return [
            'downloadable_file.required_if' => __('validation.required', ['attribute' => __('app.downloadableFile')]),
        ];
    }

}
