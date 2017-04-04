<?php

namespace Dukhanin\Panel\Query;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PanelBuilder extends EloquentBuilder
{
    public $keyName = 'id';

    public $parentKeyName = 'parent_id';

    protected $orderByDefault = true;

    protected $modelsDepths;

    public function byParent($keys)
    {
        if ($keys instanceof Collection) {
            $keys = $keys->toArray();
        }

        $keys = is_array($keys) ? $keys : [$keys];

        return $this->where(function ($query) use ($keys) {
            if (in_array(null, $keys)) {
                $query->whereNull($this->parentKeyName);
            }

            $keys = array_filter($keys, function ($key) {
                return $key !== null;
            });

            if ($keys) {
                $query->orWhereIn($this->parentKeyName, $keys);
            }

            return $query;
        });
    }

    public function nested($depthTo = null)
    {
        $this->modelsDepths = collect();

        $currentLevelKeys = collect([$this->model->exists ? $this->model->getKey() : 0]);

        $depth = 0;

        while (! $currentLevelKeys->isEmpty() && (is_null($depthTo) || $depthTo-- > 0)) {
            $currentLevelKeys = (clone $this)->byParent($currentLevelKeys)->select($this->keyName)->get()->pluck($this->keyName);

            $currentLevelKeys->each(function ($key) use ($depth) {
                $this->modelsDepths->put($key, $depth);
            });

            $depth++;
        }

        return $this->whereKey($this->modelsDepths->keys());
    }

    public function ordered($value = true)
    {
        $this->orderByDefault = (bool) $value;

        return $this;
    }

    public function unordered()
    {
        return $this->ordered(false);
    }

    public function orderReset()
    {
        $this->orders = [];
        $this->unionOrders = [];

        return $this;
    }

    public function applyScopes()
    {
        $builder = parent::applyScopes();

        $this->applyOrderDefault($builder);

        return $builder;
    }

    public function applyOrderDefault(Builder $builder)
    {
        if (! empty($builder->orders) || ! empty($builder->unionOrders)) {
            return;
        }

        if (! $this->orderByDefault) {
            return;
        }

        if (! method_exists($this->model, 'scopeOrderDefault')) {
            return;
        }

        $this->model->scopeOrderDefault($builder);
    }

    public function options($key, $depthPrefix = null)
    {
        return $this->get()->options($key, $depthPrefix);
    }

    public function tree()
    {
        return $this->get()->tree();
    }

    public function getModels($columns = ['*'])
    {
        $collection = parent::getModels($columns);

        foreach ($collection as $model) {
            if (method_exists($model, 'nestedDepth')) {
                $model->nestedDepth(array_get($this->modelsDepths, $model->getKey(), null));
            }
        }

        return $collection;
    }
}