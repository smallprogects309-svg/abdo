<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Http\Requests\Api\V1\Core\BaseFormRequest;

/**
 * Store Course Request
 * Validation للكورسات الجديدة
 */
class StoreCourseRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user is admin or instructor
        return auth()->check() && auth()->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255|unique:courses,title',
            'description' => 'required|string|min:20|max:1000',
            'content' => 'nullable|string',
            'instructor_id' => 'required|integer|exists:users,id',
            'category_id' => 'nullable|integer|exists:categories,id',
            'price' => 'required|numeric|min:0|max:999999.99',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'instructor_name' => 'required|string|max:255',
            'level' => 'required|in:beginner,intermediate,advanced',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_published' => 'nullable|boolean',
            'prerequisites' => 'nullable|array',
            'prerequisites.*' => 'integer|exists:courses,id',
            'learning_outcomes' => 'nullable|array',
            'learning_outcomes.*' => 'string|max:255',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'عنوان الكورس مطلوب',
            'title.unique' => 'عنوان الكورس موجود بالفعل',
            'description.required' => 'وصف الكورس مطلوب',
            'instructor_id.required' => 'المدرس مطلوب',
            'price.required' => 'السعر مطلوب',
            'level.required' => 'مستوى الكورس مطلوب',
        ];
    }

    /**
     * Get sanitized input
     */
    public function sanitized(): array
    {
        return [
            'title' => trim($this->input('title')),
            'description' => trim($this->input('description')),
            'content' => trim($this->input('content')),
            'instructor_id' => (int) $this->input('instructor_id'),
            'category_id' => $this->input('category_id') ? (int) $this->input('category_id') : null,
            'price' => (float) $this->input('price'),
            'discount_price' => $this->input('discount_price') ? (float) $this->input('discount_price') : null,
            'instructor_name' => trim($this->input('instructor_name')),
            'level' => $this->input('level'),
            'cover_image' => $this->file('cover_image'),
            'is_published' => (bool) $this->input('is_published', false),
            'prerequisites' => $this->input('prerequisites', []),
            'learning_outcomes' => $this->input('learning_outcomes', []),
        ];
    }
}
