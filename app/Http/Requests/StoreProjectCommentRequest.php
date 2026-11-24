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
        // Check vertical and horizontal authorization
          $project = $this->route('project');
          # Check user's policy can view the project
        return $project && $this->user()->can('view', $project);

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Column content, is set to required, string and max 2000 characters
        return [
            'content' => [
                'required',
                'string',
                'max:2000',
            ],
        ];

    }
}
