<?php

namespace Dukhanin\Panel\Features;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

trait Sort
{

    protected $sortEnabled;

    protected $sortKey;

    protected $sortNewModelToTop;


    public function initFeatureSort()
    {
        $this->sortEnabled = true;

        $this->sortKey = 'index';

        $this->sortNewModelToTop = false;
    }


    public function actionSortUp($primaryKey)
    {
        return $this->sort($primaryKey, 'up');
    }


    public function actionSortDown($primaryKey)
    {
        return $this->sort($primaryKey, 'down');
    }


    public function actionSortSlice()
    {
        $group = (array) Request::input('group');

        $models = $this->findModelsOrFail($group);

        $this->authorize('group-enable', $group);

        $primaryKeyName = $this->getModel()->getKeyName();
        $index          = intval($models->min('index'));

        $orderedList = [ ];

        foreach ($group as $modelKey) {
            $model = $models->where($primaryKeyName, $modelKey)->first();

            $orderedList[] = $model->id;

            if ($model) {
                $model->index = $index++;
                $model->save();
            }
        }

        return response()->json([
            'error'    => 0,
            'success'  => true,
            'messages' => [ ],
            'data'     => [
                'list' => $orderedList
            ]
        ]);
    }


    public function isSortEnabled()
    {
        if ( ! $this->sortEnabled) {
            return false;
        }

        if ( ! $this->order) {
            return true;
        }

        $columns = $this->getColumns();

        if ( ! isset( $columns[$this->order]['order'] )) {
            return false;
        }

        return $columns[$this->order]['order'] === $this->sortKey && ! $this->orderDesc;
    }


    protected function sort($primaryKey, $direction)
    {
        $model = $this->findModelOrFail($primaryKey);

        $this->authorize('sort', $model);

        $query                     = $this->getSortQuery();
        $query->getQuery()->orders = [ ];

        $modelIndex = intval($model->{$this->sortKey});

        if ($direction === 'up') {
            $query->where($this->sortKey, '<', $modelIndex)->orderBy($this->sortKey, 'desc');
        } else {
            $query->where($this->sortKey, '>', $modelIndex)->orderBy($this->sortKey, 'asc');
        }

        if ($neighboor = $query->first()) {
            $model->index = $neighboor->index;
            $model->save();

            $neighboor->index = $modelIndex;
            $neighboor->save();
        } elseif ($direction === 'up') {
            $model->index = -1;
        } else {
            $model->index = 9999999;
        }

        $this->resortModels();

        abort(301, '', [ 'Location' => $this->getUrl() ]);
    }


    protected function sortModelToBottom($model)
    {
        if ($this->denies('sort', $model)) {
            return false;
        }

        $max = $this->getSortQuery()->max($this->sortKey) + 1;

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

        $this->resortModels();
    }


    protected function resortModels()
    {
        // @todo @dukhanin mysql support only!
        $this->getSortQuery()->orderBy($this->sortKey,
            'asc')->update([ $this->sortKey => DB::raw('(select @i := IF(@i IS NULL, 0 , @i + 1))') ]);
    }


    protected function getSortQuery()
    {
        return $this->getQuery([ '!order', '!pages' ]);
    }

}