<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check() && !auth()->user()->isSuperAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name'  => 'required|string|max:191',
            'last_name'   => 'required|string|max:191',
            'email'       => 'required|email|max:191',
            'phone'       => 'required|string|max:191',
            'city'        => 'required|string|max:1000',
            'province'    => 'required|string|max:1000',
            'country'     => 'required|string|max:1000',
            'postal_code' => 'required|string|max:1000',
            'address'     => 'required|string|max:10000',
            'street'      => 'required|string|max:10000',
            'description' => 'nullable|string|max:4294967295',
            'logo'        => 'nullable|mimes:jpeg,jpg,png,webp'
        ];
    }

    /**
     * Validate the class instance.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        if (strtolower(request()->input('country')) !== 'canada') {
            $this->request->add(["province" => $this->request->get('province_input') ?? $this->request->get('province')]);
        }
    }
}
