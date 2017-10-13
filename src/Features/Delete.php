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
        $this->modelActions()->put('delete');

        $this->groupActions()->put('group-delete');

        if (method_exists($this, 'show') && $this->allows('delete', $this->model())) {
            $this->show()->buttons()->put('delete', [
                'url' => $this->urlTo('delete', $this->model()),
            ]);
        }
    }

    public function delete()
    {
        $this->model = $this->findModelOrFail($this->parameter('id'));

        $this->authorize('delete', $this->model);

        $this->model->delete();

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