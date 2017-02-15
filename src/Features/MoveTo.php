<?php

namespace Dukhanin\Panel\Features;

trait MoveTo
{

    protected $moveToOptions;


    protected static function routesForMoveTo(array $options = null)
    {
        static::routesMeta()->post('group-move-to/{location}', 'groupMoveTo');
    }


    public function initFeatureMoveTo()
    {
        $this->initMoveToOptions();
    }


    public function initMoveToOptions()
    {
        $this->moveToOptions = [ ];
    }


    public function moveTo($model, $location)
    {
        // extendable
    }


    public function groupMoveTo()
    {
        $group = $this->findModelsOrFail($this->input('group'));

        $this->authorize('group-move-to', $group);

        foreach ($group as $model) {
            $this->moveTo($model, $this->route('location'));
        }

        return redirect()->to($this->url());
    }


    public function moveToOptions()
    {
        if (is_null($this->moveToOptions)) {
            $this->initMoveToOptions();
        }

        return $this->moveToOptions;
    }

}