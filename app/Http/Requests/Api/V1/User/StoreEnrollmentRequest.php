<?php

namespace App\Http\Requests\Api\V1\User;

use App\Http\Requests\Api\V1\Core\BaseFormRequest;

class StoreEnrollmentRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'exists:courses,id'],
        ];
    }
}
