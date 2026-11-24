<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        //check vertical and horixontal autorizartion
        $project = $this->route('project');

        //check user's policy can view the project
        return $project && $this->user()->can('view', $project);

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */

    public function rules(): array
    {
        //column content is set to required, string and max 2000 characters
        return [
            'content' => ['required', 'string', 'max:2000']
        ];
    }
}
