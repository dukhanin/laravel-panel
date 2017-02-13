<?php

namespace Dukhanin\Panel\Features;

use Illuminate\Support\Facades\Request;

trait Delete
{

    public static function routesFeatureDelete($className)
    {
        app('router')->get('delete/{id}', "{$className}@delete");
        app('router')->post('groupDelete', "{$className}@groupDelete");
    }


    public function initFeatureDelete()
    {
        $this->modelActions['delete'] = $this->config('actions.delete');

        $this->groupActions['group-delete'] = $this->config('actions.group-delete');
    }


    public function delete($primaryKey)
    {
        $model = $this->findModelOrFail($primaryKey);

        $this->authorize('delete', $model);

        $model->delete();

        return redirect()->to($this->url());
    }


    public function groupDelete()
    {
        $group = $this->findModelsOrFail(Request::input('group'));

        $this->authorize('group-delete', $group);

        foreach ($group as $model) {
            $model->delete();
        }

        return redirect()->to($this->url());
    }
}