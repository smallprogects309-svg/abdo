<?php

namespace App\DTOs;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Base DTO Class
 * جميع DTOs ترث من هذا الـ Class
 */
abstract class BaseDTO implements Arrayable
{
    /**
     * Convert DTO to array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * Convert DTO to JSON
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Create DTO from array
     */
    public static function fromArray(array $data): static
    {
        $dto = new static();
        foreach ($data as $key => $value) {
            if (property_exists($dto, $key)) {
                $dto->$key = $value;
            }
        }
        return $dto;
    }

    /**
     * Handle null values
     */
    protected function nullSafe($value)
    {
        return $value ?? null;
    }
}
