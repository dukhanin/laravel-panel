<?php

namespace Dukhanin\Panel\Features;

use Illuminate\Support\Facades\Request;

trait EnableAndDisable
{

    function initFeatureEnableAndDisable()
    {
        $this->modelActions['enable'] = $this->config('actions.enable');

        $this->groupActions['group-enable'] = $this->config('actions.group-enable');

        $this->groupActions['group-disable'] = $this->config('actions.group-disable');
    }


    public function actionEnable($primaryKey)
    {
        $model = $this->findModelOrFail($primaryKey);

        $this->authorize('enable', $model);

        $model->enabled = true;
        $model->save();

        abort(301, '', [ 'Location' => $this->getUrl() ]);
    }


    public function actionDisable($primaryKey)
    {
        $model = $this->findModelOrFail($primaryKey);

        $this->authorize('disable', $model);

        $model->enabled = false;
        $model->save();

        abort(301, '', [ 'Location' => $this->getUrl() ]);
    }


    public function actionGroupEnable()
    {
        $group = $this->findModelsOrFail(Request::input('group'));

        $this->authorize('group-enable', $group);

        foreach ($group as $model) {
            $model->enabled = true;
            $model->save();
        }

        abort(301, '', [ 'Location' => $this->getUrl() ]);
    }


    public function actionGroupDisable()
    {
        $group = $this->findModelsOrFail(Request::input('group'));

        $this->authorize('group-disable', $group);

        foreach ($group as $model) {
            $model->enabled = false;
            $model->save();
        }

        abort(301, '', [ 'Location' => $this->getUrl() ]);
    }


    public function applyEachRowDisabled(&$row)
    {
        if ( ! $row['model']->enabled) {
            array_set($row, 'attributes.class', 'inactive');
        }
    }
}