<?php

namespace Dukhanin\Panel\Features;

use Illuminate\Support\Facades\Request;

trait EnableAndDisable
{

    protected $enabledKey;


    function initFeatureEnableAndDisable()
    {
        $this->initEnabledKey();

        $this->modelActions['enable'] = $this->config('actions.enable');

        $this->groupActions['group-enable'] = $this->config('actions.group-enable');

        $this->groupActions['group-disable'] = $this->config('actions.group-disable');
    }


    public function initEnabledKey()
    {
        $this->enabledKey = 'enabled';
    }


    public function getEnabledKey()
    {
        if (is_null($this->enabledKey)) {
            $this->initEnabledKey();
        }

        return $this->enabledKey;
    }


    public function actionEnable($primaryKey)
    {
        $model = $this->findModelOrFail($primaryKey);

        $this->authorize('enable', $model);

        $model->{$this->enabledKey} = true;
        $model->save();

        abort(301, '', [ 'Location' => $this->getUrl() ]);
    }


    public function actionDisable($primaryKey)
    {
        $model = $this->findModelOrFail($primaryKey);

        $this->authorize('disable', $model);

        $model->{$this->enabledKey} = false;
        $model->save();

        abort(301, '', [ 'Location' => $this->getUrl() ]);
    }


    public function actionGroupEnable()
    {
        $group = $this->findModelsOrFail(Request::input('group'));

        $this->authorize('group-enable', $group);

        foreach ($group as $model) {
            $model->{$this->enabledKey} = true;
            $model->save();
        }

        abort(301, '', [ 'Location' => $this->getUrl() ]);
    }


    public function actionGroupDisable()
    {
        $group = $this->findModelsOrFail(Request::input('group'));

        $this->authorize('group-disable', $group);

        foreach ($group as $model) {
            $model->{$this->enabledKey} = false;
            $model->save();
        }

        abort(301, '', [ 'Location' => $this->getUrl() ]);
    }


    public function applyEachRowDisabled(&$row)
    {
        if ( ! $row['model']->{$this->enabledKey}) {
            array_set($row, 'attributes.class', 'inactive');
        }
    }
}