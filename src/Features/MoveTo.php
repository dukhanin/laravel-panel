<?php

namespace Dukhanin\Panel\Features;

use Illuminate\Support\Facades\Request;

trait MoveTo
{

    protected $moveTo;


    public function initFeatureMoveTo()
    {
        $this->initMoveTo();
    }


    public function actionGroupMoveTo($location)
    {
        $group = $this->findModelsOrFail(Request::input('group'));

        $this->authorize('group-move-to', $group);

        foreach ($group as $model) {
            $this->moveTo($model, $location);
        }

        abort(301, '', [ 'Location' => $this->getUrl() ]);
    }


    public function initMoveTo()
    {
        $this->moveTo = [ ];
    }


    public function getMoveTo()
    {
        if (is_null($this->moveTo)) {
            $this->initMoveTo();
        }

        return $this->moveTo;
    }


    public function moveTo($model, $location)
    {

    }

}