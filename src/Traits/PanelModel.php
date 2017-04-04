<?php

namespace Dukhanin\Panel\Traits;

use Dukhanin\Panel\Collections\PanelModelsCollection;
use Dukhanin\Panel\Query\PanelBuilder;

trait PanelModel
{
    protected $nestedCollection;

    public $nestedDepth;

    public function getParentKeyName()
    {
        return 'parent_id';
    }

    public function newEloquentBuilder($query)
    {
        $builder = new PanelBuilder($query);

        $builder->keyName = $this->getKeyName();
        $builder->parentKeyName = $this->getParentKeyName();

        return $builder;
    }

    public function initNestedCollection()
    {
        $this->nestedCollection = $this->newCollection();
    }

    public function nestedCollection()
    {
        if (is_null($this->nestedCollection)) {
            $this->initNestedCollection();
        }

        return $this->nestedCollection;
    }

    public function nestedDepth($value = null)
    {
        if (func_num_args() === 0) {
            return $this->nestedDepth;
        }

        $this->nestedDepth = is_null($value) ? null : intval($value);
    }

    public function newCollection(array $models = [])
    {
        $collection = new PanelModelsCollection($models);

        $collection->keyName = $this->getKeyName();
        $collection->parentKeyName = $this->getParentKeyName();

        return $collection;
    }
}