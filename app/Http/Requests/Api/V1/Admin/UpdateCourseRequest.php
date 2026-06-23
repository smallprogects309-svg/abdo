<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Http\Requests\Api\V1\Core\BaseFormRequest;
use Illuminate\Validation\Rule;

/**
 * Update Course Request
 * Validation للكورسات المحدثة
 */
class UpdateCourseRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $courseId = $this->route('course') ?? $this->route('id');
        
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('courses', 'title')->ignore($courseId)],
            'description' => 'sometimes|required|string|min:20|max:1000',
            'content' => 'nullable|string',
            'instructor_id' => 'sometimes|required|integer|exists:users,id',
            'category_id' => 'nullable|integer|exists:categories,id',
            'price' => 'sometimes|required|numeric|min:0|max:999999.99',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'instructor_name' => 'sometimes|required|string|max:255',
            'level' => 'sometimes|required|in:beginner,intermediate,advanced',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_published' => 'nullable|boolean',
            'prerequisites' => 'nullable|array',
            'prerequisites.*' => 'integer|exists:courses,id',
            'learning_outcomes' => 'nullable|array',
            'learning_outcomes.*' => 'string|max:255',
        ];
    }

    /**
     * Get sanitized input
     */
    public function sanitized(): array
    {
        return [
            'title' => $this->input('title') ? trim($this->input('title')) : null,
            'description' => $this->input('description') ? trim($this->input('description')) : null,
            'content' => $this->input('content') ? trim($this->input('content')) : null,
            'instructor_id' => $this->input('instructor_id') ? (int) $this->input('instructor_id') : null,
            'category_id' => $this->input('category_id') ? (int) $this->input('category_id') : null,
            'price' => $this->input('price') ? (float) $this->input('price') : null,
            'discount_price' => $this->input('discount_price') ? (float) $this->input('discount_price') : null,
            'instructor_name' => $this->input('instructor_name') ? trim($this->input('instructor_name')) : null,
            'level' => $this->input('level'),
            'cover_image' => $this->file('cover_image'),
            'is_published' => $this->has('is_published') ? (bool) $this->input('is_published') : null,
            'prerequisites' => $this->input('prerequisites', []),
            'learning_outcomes' => $this->input('learning_outcomes', []),
        ];
    }
}
