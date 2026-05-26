<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\ValidTitleSemantics;

class StoreArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        // Only admins and writers are allowed
        return $user && in_array($user->role, ['admin', 'writer']);
    }

    /**
     * Prepare data for validation (Sanitization).
     */
    protected function prepareForValidation(): void
    {   
        // Trim whitespace and normalize title to lowercase for consistent validation
        $cleanedTitle = preg_replace('/\s+/', ' ', trim($this->title));
        // Convert to lowercase using mb_strtolower for UTF-8 support
        $cleanedTitle = mb_strtolower($cleanedTitle, 'UTF-8'); 

        $this->merge([
            'title' => $cleanedTitle,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'min:10',
                Rule::unique('articles', 'title')->ignore($this->route('article')),
                new ValidTitleSemantics(), // Applied the independent custom rule class
            ],
            
            'content' => ['required', 'string', 'min:100'],
            
            'status' => ['required', 'string', Rule::in(['draft', 'published', 'archived'])],
            
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The article title is mandatory.',
            'title.min' => 'The title must be at least 10 characters long.',
            'title.unique' => 'This title has already been registered in the system.',
            'content.min' => 'The article body content must be at least 100 characters long.',
            'status.in' => 'The status field must strictly be draft, published, or archived.',
            'tags.*.exists' => 'One or more selected tags do not exist in our records.',
        ];
    }
}