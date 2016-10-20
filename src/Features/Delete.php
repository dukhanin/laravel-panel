<?php

namespace Dukhanin\Panel\Features;

use Illuminate\Support\Facades\Request;

trait Delete
{

    public function initFeatureDelete()
    {
        $this->modelActions['delete'] = $this->config('actions.delete');

        $this->groupActions['group-delete'] = $this->config('actions.group-delete');
    }


    public function actionDelete($primaryKey)
    {
        $model = $this->findModelOrFail($primaryKey);

        $this->authorize('delete', $model);

        $model->delete();

        abort(301, '', [ 'Location' => $this->getUrl() ]);
    }


    public function actionGroupDelete()
    {
        $group = $this->findModelsOrFail(Request::input('group'));

        $this->authorize('group-delete', $group);

        foreach ($group as $model) {
            $model->delete();
        }

        abort(301, '', [ 'Location' => $this->getUrl() ]);
    }
}