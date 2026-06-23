<?php

namespace App\Repositories\Core;

use App\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\LazyCollection;

/**
 * Base Repository
 * مع Eager Loading + LazyCollections للبيانات الضخمة
 */
abstract class BaseRepository implements RepositoryInterface
{
    protected Model $model;
    protected array $relations = [];
    protected array $withCount = [];
    protected int $chunkSize = 1000;  // حجم الـ chunk للـ lazy loading

    /**
     * Set the model
     */
    public function setModel(Model $model): static
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Set chunk size for lazy loading
     */
    public function setChunkSize(int $size): static
    {
        $this->chunkSize = $size;
        return $this;
    }

    /**
     * Get all records with eager loading
     */
    public function all(array $columns = ['*'])
    {
        return $this->applyRelations($this->model)
            ->select($columns)
            ->get();
    }

    /**
     * Get paginated records
     */
    public function paginate(int $perPage = 15, array $columns = ['*'])
    {
        return $this->applyRelations($this->model)
            ->select($columns)
            ->paginate($perPage);
    }

    /**
     * Find by ID with eager loading
     */
    public function find(int $id, array $columns = ['*'])
    {
        return $this->applyRelations($this->model)
            ->select($columns)
            ->findOrFail($id);
    }

    /**
     * Find by attribute
     */
    public function findBy(string $attribute, $value, array $columns = ['*'])
    {
        return $this->applyRelations($this->model)
            ->select($columns)
            ->where($attribute, $value)
            ->first();
    }

    /**
     * Find all by attribute
     */
    public function findAllBy(string $attribute, $value, array $columns = ['*'])
    {
        return $this->applyRelations($this->model)
            ->select($columns)
            ->where($attribute, $value)
            ->get();
    }

    /**
     * Create new record
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update record
     */
    public function update(int $id, array $data): Model
    {
        $record = $this->find($id);
        $record->update($data);
        return $record->refresh();
    }

    /**
     * Delete record
     */
    public function delete(int $id): bool
    {
        return $this->find($id)->delete();
    }

    /**
     * Check if record exists
     */
    public function exists(int $id): bool
    {
        return $this->model->where('id', $id)->exists();
    }

    /**
     * Get count
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * Search records
     */
    public function search(string $query, array $columns = ['*'])
    {
        return $this->applyRelations($this->model)
            ->select($columns)
            ->where('name', 'like', "%{$query}%")
            ->orWhere('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->get();
    }

    /**
     * ⚡ Lazy Collection Methods - للبيانات الضخمة
     */

    /**
     * Get all records as lazy collection
     * ✅ لا تحمل جميع البيانات في الذاكرة دفعة واحدة
     * ✅ معالجة فورية مع chunks
     * ✅ استهلاك رام منخفض جداً
     */
    public function lazy(array $columns = ['*']): LazyCollection
    {
        return $this->applyRelations($this->model)
            ->select($columns)
            ->lazy($this->chunkSize);
    }

    /**
     * Get records by attribute as lazy
     */
    public function lazyBy(string $attribute, $value, array $columns = ['*']): LazyCollection
    {
        return $this->model
            ->select($columns)
            ->where($attribute, $value)
            ->lazy($this->chunkSize);
    }

    /**
     * Process large dataset with callback
     * مثال: معالجة 1 مليون record بدون استهلاك الرام
     */
    public function eachLazy(callable $callback, array $columns = ['*']): void
    {
        $this->lazy($columns)->each($callback);
    }

    /**
     * Process with pluck (memory efficient)
     * مثال: استخراج أسماء مليون مستخدم بكفاءة
     */
    public function lazyPluck(string $value, string $key = null): LazyCollection
    {
        return $this->model->select($key ? [$key, $value] : [$value])
            ->lazy($this->chunkSize)
            ->pluck($value, $key);
    }

    /**
     * Filter large dataset efficiently
     */
    public function lazyFilter(callable $callback, array $columns = ['*']): LazyCollection
    {
        return $this->lazy($columns)->filter($callback);
    }

    /**
     * Map large dataset efficiently
     */
    public function lazyMap(callable $callback, array $columns = ['*']): LazyCollection
    {
        return $this->lazy($columns)->map($callback);
    }

    /**
     * Export large dataset efficiently
     * للـ CSV export, reporting, bulk operations
     */
    public function exportLazy(array $columns = ['*']): LazyCollection
    {
        return $this->applyRelations($this->model)
            ->select($columns)
            ->lazy($this->chunkSize);
    }

    /**
     * Chunk processing
     * معالجة البيانات في chunks منفصلة
     */
    public function chunk(int $size, callable $callback): bool
    {
        return $this->model->chunk($size, $callback);
    }

    /**
     * Apply eager loading and counts
     * منع N+1 queries
     */
    protected function applyRelations(Builder $query): Builder
    {
        if (!empty($this->relations)) {
            $query->with($this->relations);
        }

        if (!empty($this->withCount)) {
            $query->withCount($this->withCount);
        }

        return $query;
    }

    /**
     * Set relations to load
     */
    public function with(array $relations): static
    {
        $this->relations = $relations;
        return $this;
    }

    /**
     * Set counts to load
     */
    public function withCount(array $relations): static
    {
        $this->withCount = $relations;
        return $this;
    }

    /**
     * Get the query builder
     */
    public function query(): Builder
    {
        return $this->model->query();
    }

    /**
     * Reset relations
     */
    protected function resetRelations(): void
    {
        $this->relations = [];
        $this->withCount = [];
    }
}
