<?php

namespace Dukhanin\Panel\Features;

use Illuminate\Support\Facades\Request;

trait Delete
{

    protected static function routesFeatureDelete(array $options = null)
    {
        app('router')->get('delete/{id}', "{$options['class']}@delete")->name($options['as'] ? "{$options['as']}.delete" : null);
        app('router')->post('groupDelete', "{$options['class']}@groupDelete")->name($options['as'] ? "{$options['as']}.groupDelete" : null);
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