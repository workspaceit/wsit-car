<?php

namespace Plugins\TaskManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TmTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title'       =>'required|string|max:120',
            'status'      => 'required|string',
            'assigner_id' => 'nullable|integer',
            'priority'    => 'required|integer',
            'links'       => 'nullable|array',
            'description' => 'nullable|string|max:30000',
            'reminder'    => 'nullable',
            'delivery_date'=>'nullable',
        ];
    }
}
