<?php

namespace Dukhanin\Panel\Traits;

use Dukhanin\Panel\PanelTreeDecorator;

trait PanelTreeTrait
{

    use PanelListTrait;

    public $parentKey;

    public $parentKeyValue;


    public function initView()
    {
        $this->view = view($this->config('views') . '.tree', [ 'panel' => $this->decorator() ]);
    }


    public function initParentKey()
    {
        $this->parentKey = 'parent_id';
    }


    public function initParentKeyValue()
    {
        $this->parentKeyValue = 0;
    }


    public function initModelActions()
    {
        $this->modelActions->push($this->config('actions.append'));
    }


    public function initDecorator()
    {
        $this->decorator = new PanelTreeDecorator($this);
    }


    public function parentKey()
    {
        if (is_null($this->parentKey)) {
            $this->initParentKey();
        }

        return $this->parentKey;
    }


    public function parentKeyValue()
    {
        if (is_null($this->parentKeyValue)) {
            $this->initParentKeyValue();
        }

        return $this->parentKeyValue;
    }


    public function queryBranch($parentKeyValue = null, array $apply = [ '*' ])
    {
        return $this->query($apply)->where($this->parentKey(),
            is_null($parentKeyValue) ? $this->parentKeyValue() : $parentKeyValue);
    }


    public function items(array $apply = [ '*' ])
    {
        return $this->queryBranch(null, $apply)->get();
    }


    protected function newModel()
    {
        $model = clone $this->model();

        $parentKeyValue = $this->input('appendTo');

        if ($parentKeyValue) {
            $parent = $this->findModel($parentKeyValue);

            if ( ! $parent || $this->denies('append', [ $parent, $model ])) {
                $parentKeyValue = null;
            }
        }

        if (is_null($parentKeyValue)) {
            $parentKeyValue = $this->parentKeyValue();
        }

        $model->{$this->parentKey()} = $parentKeyValue;

        return $model;
    }


    protected function sortQuery()
    {
        return $this->queryBranch($this->model()->{$this->parentKey()}, [ '!order' ]);
    }

}