<?php

namespace App\Http\Requests\Project;

use App\Http\Requests\CoreRequest;
use App\Models\CustomField;

class StoreProject extends CoreRequest
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

        $rules = [
            'project_name' => 'required|max:150',
            'start_date' => 'required|date_format:"' . $setting->date_format . '"',
            'hours_allocated' => 'nullable|numeric',
            'client_id' => 'requiredIf:client_view_task,true',
        ];

        if (!request()->public) {
            $rules['user_id.0'] = 'required';
        }

        if (!$this->has('without_deadline')) {
            $rules['deadline'] = 'required|date_format:"' . $setting->date_format . '"|after_or_equal:start_date';
        }

        if ($this->project_budget != '') {
            $rules['project_budget'] = 'numeric';
            $rules['currency_id'] = 'required';
        }

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
            'user_id.0.required' => __('messages.atleastOneValidation')
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
