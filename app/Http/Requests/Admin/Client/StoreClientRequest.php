<?php

namespace App\Http\Requests\Admin\Client;

use App\Http\Requests\CoreRequest;
use App\Models\CustomField;

class StoreClientRequest extends CoreRequest
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
        $rules = [
            'name' => 'required',
            'email' => 'nullable|required_if:login,enable|email:rfc|unique:users',
            'password' => 'nullable|required_if:login,enable|min:8',
            'slack_username' => 'nullable|unique:employee_details,slack_username',
            'website' => 'nullable|url',
            'country' => 'required_with:mobile',
            'mobile' => 'nullable|numeric'
        ];

        if (request()->get('custom_fields_data')) {
            $fields = request()->get('custom_fields_data');

            foreach ($fields as $key => $value) {
                $idarray = explode('_', $key);
                $id = end($idarray);
                $customField = CustomField::findOrFail($id);

                if ($customField->required == 'yes' && (is_null($value) || $value == '')) {
                    $rules['custom_fields_data['.$key.']'] = 'required';
                }
            }
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'website.url' => 'The website format is invalid. Add https:// or http to url'
        ];
    }

    public function attributes()
    {
        $attributes = [];

        if (request()->get('custom_fields_data')) {
            $fields = request()->get('custom_fields_data');

            foreach ($fields as $key => $value) {
                $idarray = explode('_', $key);
                $id = end($idarray);
                $customField = CustomField::findOrFail($id);

                if ($customField->required == 'yes') {
                    $attributes['custom_fields_data['.$key.']'] = $customField->label;
                }
            }
        }

        return $attributes;
    }

}
