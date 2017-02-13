<?php

namespace Dukhanin\Panel\Features;

trait MoveTo
{

    protected $moveToOptions;


    public static function routesFeatureMoveTo($className)
    {
        app('router')->post('groupMoveTo/{id}', "{$className}@groupMoveTo");
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


    public function groupMoveTo($location)
    {
        $group = $this->findModelsOrFail($this->input('group'));

        $this->authorize('group-move-to', $group);

        foreach ($group as $model) {
            $this->moveTo($model, $location);
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