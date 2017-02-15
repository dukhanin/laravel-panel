<?php

namespace Dukhanin\Panel\Features;

trait Delete
{

    protected static function routesForDelete(array $options = null)
    {
        static::routesMeta()->get('delete/{id}', 'delete');

        static::routesMeta()->post('group-delete', 'groupDelete');
    }


    public function initFeatureDelete()
    {
        $this->modelActions['delete'] = $this->config('actions.delete');

        $this->groupActions['group-delete'] = $this->config('actions.group-delete');
    }


    public function delete()
    {
        $model = $this->findModelOrFail($this->route('id'));

        $this->authorize('delete', $model);

        $model->delete();

        return redirect()->to($this->url());
    }


    public function groupDelete()
    {
        $group = $this->findModelsOrFail($this->input('group'));

        $this->authorize('group-delete', $group);

        foreach ($group as $model) {
            $model->delete();
        }

        return redirect()->to($this->url());
    }
}