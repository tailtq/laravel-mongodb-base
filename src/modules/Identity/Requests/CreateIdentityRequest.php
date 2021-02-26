<?php

namespace Modules\Identity\Requests;

use Infrastructure\Requests\BaseCRUDRequest;

class CreateIdentityRequest extends BaseCRUDRequest
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
            'name' => 'required|string|max:255',
            'info' => 'nullable',
            'status' => 'nullable',
            'card_number' => 'required',
            'images' => 'required',
            'images.*.url' => 'required|string',
        ];
    }
}
