<?php

namespace Dukhanin\Panel\Features;

use Illuminate\Support\Facades\Request;

trait DisableAndEnable
{

    protected $disabledKey;


    function initFeatureDisableAndEnable()
    {
        $this->initDisabledKey();

        $this->modelActions['disable'] = $this->config('actions.disable');

        $this->groupActions['group-enable'] = $this->config('actions.group-enable');

        $this->groupActions['group-disable'] = $this->config('actions.group-disable');
    }


    public function initDisabledKey()
    {
        $this->disabledKey = 'disabled';
    }


    public function getDisabledKey()
    {
        if (is_null($this->disabledKey)) {
            $this->initDisabledKey();
        }

        return $this->disabledKey;
    }


    public function actionEnable($primaryKey)
    {
        $model = $this->findModelOrFail($primaryKey);

        $this->authorize('enable', $model);

        $model->{$this->disabledKey} = false;
        $model->save();

        abort(301, '', [ 'Location' => $this->getUrl() ]);
    }


    public function actionDisable($primaryKey)
    {
        $model = $this->findModelOrFail($primaryKey);

        $this->authorize('disable', $model);

        $model->{$this->disabledKey} = true;
        $model->save();

        abort(301, '', [ 'Location' => $this->getUrl() ]);
    }


    public function actionGroupEnable()
    {
        $group = $this->findModelsOrFail(Request::input('group'));

        $this->authorize('group-enable', $group);

        foreach ($group as $model) {
            $model->{$this->disabledKey} = false;
            $model->save();
        }

        abort(301, '', [ 'Location' => $this->getUrl() ]);
    }


    public function actionGroupDisable()
    {
        $group = $this->findModelsOrFail(Request::input('group'));

        $this->authorize('group-disable', $group);

        foreach ($group as $model) {
            $model->{$this->disabledKey} = true;
            $model->save();
        }

        abort(301, '', [ 'Location' => $this->getUrl() ]);
    }


    public function applyEachRowDisabled(&$row)
    {
        if ($row['model']->{$this->disabledKey}) {
            array_set($row, 'attributes.class', 'inactive');
        }
    }
}