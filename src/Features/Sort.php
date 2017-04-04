<?php

namespace Dukhanin\Panel\Features;

use Dukhanin\Panel\PanelTree;

trait Sort
{
    protected $modelToSort;

    protected $sortEnabled;

    protected $sortKey;

    protected $sortNewModelToTop;

    protected static function routesForSort(array $options = null)
    {
        app('router')->get('sort-up/{id}', '\\'.static::class.'@sortUp')->name('sortUp');

        app('router')->get('sort-down/{id}', '\\'.static::class.'@sortDown')->name('sortDown');

        app('router')->post('sort-slice', '\\'.static::class.'@sortSlice')->name('sortSlice');
    }

    public function initFeatureSort()
    {
        $this->sortEnabled = true;

        $this->sortKey = 'index';

        $this->sortNewModelToTop = false;
    }

    public function sortUp()
    {
        return $this->sort($this->parameter('id'), 'up');
    }

    public function sortDown()
    {
        return $this->sort($this->parameter('id'), 'down');
    }

    public function sortSlice()
    {
        $group = (array) $this->input('group');

        $models = $this->findModelsOrFail($group);

        $this->authorize('group-enable', $models);

        $primaryKeyName = $this->model()->getKeyName();
        $index = intval($models->min('index'));

        $orderedList = [];

        foreach ($group as $modelKey) {
            $model = $models->where($primaryKeyName, $modelKey)->first();

            $orderedList[] = $model->id;

            if ($model) {
                $model->index = $index++;
                $model->save();
            }
        }

        return response()->json([
            'error' => 0,
            'success' => true,
            'messages' => [],
            'data' => [
                'list' => $orderedList,
            ],
        ]);
    }

    public function isSortEnabled()
    {
        if (! $this->sortEnabled) {
            return false;
        }

        if (empty($this->order)) {
            return true;
        }

        $columns = $this->columns()->resolved();

        if (isset($this->order) && ! isset($columns[$this->order]['order'])) {
            return false;
        }

        return $columns[$this->order]['order'] === $this->sortKey && ! $this->orderDesc;
    }

    public function applyQueryOrderDefault($query)
    {
        if (! method_exists($this, 'order') || empty($this->order())) {
            $query->orderBy($this->sortKey, 'asc');
        }
    }

    protected function sort($primaryKey, $direction)
    {
        $this->modelToSort = $this->findModelOrFail($primaryKey);

        $this->authorize('sort', $this->modelToSort);

        $query = $this->sortQuery();

        $query->getQuery()->orders = [];

        $modelIndex = intval($this->modelToSort->{$this->sortKey});

        if ($direction === 'up') {
            $query->where($this->sortKey, '<', $modelIndex)->orderBy($this->sortKey, 'desc');
        } else {
            $query->where($this->sortKey, '>', $modelIndex)->orderBy($this->sortKey, 'asc');
        }

        if ($neighboor = $query->first()) {
            $this->modelToSort->index = $neighboor->index;
            $this->modelToSort->save();

            $neighboor->index = $modelIndex;
            $neighboor->save();
        } elseif ($direction === 'up') {
            $this->modelToSort->index = -1;
        } else {
            $this->modelToSort->index = 9999999;
        }

        $this->resortModels();

        return redirect()->to($this->url());
    }

    protected function sortModelToBottom($model)
    {
        if ($this->denies('sort', $model)) {
            return false;
        }

        $max = $this->sortQuery($model)->max($this->sortKey) + 1;

        $model->{$this->sortKey} = $max;
        $model->save();
    }

    protected function sortModelToTop($model)
    {
        if ($this->denies('sort', $model)) {
            return false;
        }

        $model->{$this->sortKey} = -1;
        $model->save();

        $this->resortModels($model);
    }

    protected function resortModels()
    {
        $index = 0;

        $this->sortQuery()->select(['id'])->orderBy($this->sortKey, 'asc')->chunk(50, function ($models) use ($index) {
            foreach ($models as $model) {
                $model->index = $index++;
                $model->save();
            }
        });
    }

    protected function sortQuery()
    {
        if ($this instanceof PanelTree) {
            return $this->queryBranch($this->modelToSort->{$this->parentKey()}, ['!order', '!page']);
        }

        return $this->query(['!order', '!page']);
    }
}