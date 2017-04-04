<?php

namespace Dukhanin\Panel\Features;

trait Delete
{
    protected static function routesForDelete(array $options = null)
    {
        app('router')->get('delete/{id}', static::routeAction('delete'))->name('delete');

        app('router')->post('group-delete', static::routeAction('groupDelete'))->name('groupDelete');
    }

    public function initFeatureDelete()
    {
        $this->modelActions['delete'] = $this->config('actions.delete');

        $this->groupActions['group-delete'] = $this->config('actions.group-delete');
    }

    public function delete()
    {
        $model = $this->findModelOrFail($this->parameter('id'));

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