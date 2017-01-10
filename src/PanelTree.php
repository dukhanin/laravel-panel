<?php

namespace Dukhanin\Panel;

class PanelTree extends PanelList
{

    public $parentKey;

    public $parentKeyValue;


    public function initView()
    {

        $this->view = view($this->config('views') . '.tree', [ 'decorator' => $this->getDecorator() ]);
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
        $this->modelActions = [ $this->config('actions.append') ];
    }


    public function initDecorator()
    {
        $this->decorator = new PanelTreeDecorator($this);
    }


    public function getParentKey()
    {
        if (is_null($this->parentKey)) {
            $this->initParentKey();
        }

        return $this->parentKey;
    }


    public function getParentKeyValue()
    {
        if (is_null($this->parentKeyValue)) {
            $this->initParentKeyValue();
        }

        return $this->parentKeyValue;
    }


    public function getQueryBranch($parentKeyValue = null, array $apply = [ '*' ])
    {
        return $this->getQuery($apply)->where($this->getParentKey(),
            is_null($parentKeyValue) ? $this->getParentKeyValue() : $parentKeyValue);
    }


    public function getList(array $apply = [ '*' ])
    {
        return $this->getQueryBranch(null, $apply)->get();
    }


    protected function newModel()
    {
        $model = parent::newModel();

        $parentKeyValue = $this->getRequestAttribute('appendTo');

        if ($parentKeyValue) {
            $parent = $this->findModel($parentKeyValue);

            if ( ! $parent || $this->denies('append', [ $parent, $model ])) {
                $parentKeyValue = null;
            }
        }

        if (is_null($parentKeyValue)) {
            $parentKeyValue = $this->getParentKeyValue();
        }

        $model->{$this->getParentKey()} = $parentKeyValue;

        return $model;
    }


    protected function getSortQuery()
    {
        return $this->getQueryBranch($this->model->{$this->getParentKey()}, [ '!order' ]);
    }

}