<?php

namespace App\Traits;

/**
 * Filterable Trait
 * 
 * للـ filtering مرن على Models
 * 
 * في Model استخدم:
 * protected $searchable = ['title', 'description'];
 * protected $filterable = ['status', 'category_id'];
 * protected $dateFilters = ['created_at', 'published_at'];
 */
trait Filterable
{
    /**
     * Scope: Filter with advanced options
     */
    public function scopeFilter($query, array $filters = [])
    {
        // Search in multiple fields
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $searchFields = $this->getSearchFields();
            
            $query->where(function ($q) use ($searchFields, $search) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        // Exact field filtering
        if (isset($filters['searchFields']) && is_array($filters['searchFields'])) {
            $search = $filters['search'] ?? '';
            if (!empty($search)) {
                $query->where(function ($q) use ($filters, $search) {
                    foreach ($filters['searchFields'] as $field) {
                        $q->orWhere($field, 'like', "%{$search}%");
                    }
                });
            }
        }

        // Apply standard filters (status, category, etc)
        $filterableFields = $this->getFilterableFields();
        foreach ($filterableFields as $field) {
            if (isset($filters[$field]) && !empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }

        // Range filters (price_min, price_max, etc)
        $this->applyRangeFilters($query, $filters);

        // Date filters
        $this->applyDateFilters($query, $filters);

        // Relationship filtering
        if (isset($filters['with']) && is_array($filters['with'])) {
            $query->with($filters['with']);
        }

        // Sorting
        if (isset($filters['sort']) && !empty($filters['sort'])) {
            $sort = $filters['sort'];
            $direction = $filters['sort_direction'] ?? 'desc';
            
            // Validate direction
            $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'desc';
            
            $query->orderBy($sort, $direction);
        } else {
            // Default sort
            $query->orderBy($this->getDefaultSort(), 'desc');
        }

        return $query;
    }

    /**
     * Get searchable fields from model or use default
     */
    protected function getSearchFields(): array
    {
        if (isset($this->searchable) && is_array($this->searchable)) {
            return $this->searchable;
        }

        // Default to 'name' or 'title'
        if (in_array('title', $this->getFillable())) {
            return ['title'];
        }

        return ['name'] ?? [];
    }

    /**
     * Get filterable fields from model
     */
    protected function getFilterableFields(): array
    {
        if (isset($this->filterable) && is_array($this->filterable)) {
            return $this->filterable;
        }

        return ['status'] ?? [];
    }

    /**
     * Get date filterable fields
     */
    protected function getDateFilterFields(): array
    {
        if (isset($this->dateFilters) && is_array($this->dateFilters)) {
            return $this->dateFilters;
        }

        return ['created_at', 'updated_at'];
    }

    /**
     * Get default sort field
     */
    protected function getDefaultSort(): string
    {
        return 'created_at';
    }

    /**
     * Apply range filters (price_min, price_max, etc)
     */
    protected function applyRangeFilters($query, array $filters): void
    {
        // للـ numeric fields عل شكل field_min و field_max
        // مثال: price_min, price_max أو enrollments_min, enrollments_max
        
        foreach ($filters as $key => $value) {
            if (str_ends_with($key, '_min')) {
                $field = str_replace('_min', '', $key);
                if (!empty($value)) {
                    $query->where($field, '>=', $value);
                }
            }

            if (str_ends_with($key, '_max')) {
                $field = str_replace('_max', '', $key);
                if (!empty($value)) {
                    $query->where($field, '<=', $value);
                }
            }
        }
    }

    /**
     * Apply date filters (created_from, created_to, etc)
     */
    protected function applyDateFilters($query, array $filters): void
    {
        $dateFields = $this->getDateFilterFields();

        foreach ($dateFields as $field) {
            $fromKey = $field . '_from';
            $toKey = $field . '_to';

            // From date
            if (isset($filters[$fromKey]) && !empty($filters[$fromKey])) {
                $query->whereDate($field, '>=', $filters[$fromKey]);
            }

            // To date
            if (isset($filters[$toKey]) && !empty($filters[$toKey])) {
                $query->whereDate($field, '<=', $filters[$toKey]);
            }
        }
    }

    /**
     * Scope: Simple keyword search
     */
    public function scopeSearch($query, string $keyword)
    {
        if (empty($keyword)) {
            return $query;
        }

        $searchFields = $this->getSearchFields();

        return $query->where(function ($q) use ($searchFields, $keyword) {
            foreach ($searchFields as $field) {
                $q->orWhere($field, 'like', "%{$keyword}%");
            }
        });
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeByDateRange($query, string $dateField, $from, $to)
    {
        return $query->whereDate($dateField, '>=', $from)
                     ->whereDate($dateField, '<=', $to);
    }

    /**
     * Scope: Filter by price range
     */
    public function scopeByPriceRange($query, float $min, float $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }
}
